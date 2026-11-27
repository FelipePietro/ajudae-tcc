<?php

namespace App\Support;

use Illuminate\Http\Response;

/** Synthetic administrative cases. No model, credential or document file is loaded here. */
final class VisualFourFixtures
{
    public const PAGES = [
        'admin' => ['Painel de administração', null, '▦'],
        'fila-eventos' => ['Fila de eventos', 'eventos.aprovar', '📋'],
        'cadastros-ong' => ['Cadastros de ONG', 'ongs.validar', '🏢'],
        'upgrades-org' => ['Promoções a organizador', 'organizadores.validar', '⬆'],
        'candidatos' => ['Validação de Pessoas', 'usuarios.validar', '👥'],
        'denuncias' => ['Ocorrências', 'usuarios.moderar', '🚩'],
        'solicitacoes-lgpd' => ['Solicitações LGPD', 'lgpd.visualizar', '🔒'],
        'usuarios' => ['Usuários', 'usuarios.validar', '👤'],
        'relatorios' => ['Relatórios', 'admin.logs.visualizar', '📊'],
        'log-acoes' => ['Log de ações', 'admin.logs.visualizar', '📜'],
        'configuracoes' => ['Configurações', null, '⚙'],
    ];

    public const STATUSES = [
        'reanalise' => 'Aguardando reanálise',
        'aguardando a confirmação' => 'Aguardando aprovação', 'aguardando aprovação' => 'Aguardando aprovação',
        'aguardando_validacao' => 'Aguardando validação', 'ativo' => 'Ativo', 'publicado' => 'Publicado',
        'reprovado' => 'Reprovado', 'cancelado' => 'Cancelado', 'finalizado' => 'Finalizado', 'pendente' => 'Pendente',
        'em_analise' => 'Em análise', 'correcao_solicitada' => 'Correção solicitada', 'aprovado' => 'Aprovado',
        'aprovada' => 'Aprovada', 'reprovada' => 'Reprovada', 'reprovado_definitivamente' => 'Reprovado definitivamente',
        'rejeitado' => 'Rejeitado', 'renovacao' => 'Renovação pendente', 'historico' => 'Histórico',
        'rejeitada' => 'Rejeitada', 'concluida' => 'Concluída', 'recusada' => 'Recusada',
    ];

    public const TONES = [
        'publicado' => 'verde', 'finalizado' => 'verde', 'ativo' => 'verde', 'aprovado' => 'verde', 'aprovada' => 'verde', 'concluida' => 'verde',
        'reprovado' => 'vermelho', 'reprovada' => 'vermelho', 'reprovado_definitivamente' => 'vermelho', 'rejeitado' => 'vermelho', 'rejeitada' => 'vermelho', 'recusada' => 'vermelho',
        'cancelado' => 'cinza', 'historico' => 'cinza',
        'em_analise' => 'azul', 'correcao_solicitada' => 'azul',
    ];

    public const DOCUMENTS = ['Documento de identidade', 'Certidão de antecedentes'];

    public static function displayState(array $record): string
    {
        return ($record['reanalysis'] ?? false) && $record['status'] === 'aguardando a confirmação'
            ? 'reanalise' : $record['status'];
    }

    public static function page(string $page): Response
    {
        $request = request();
        $context = $request->query('contexto');
        $variant = $request->query('visao');
        $variant = is_string($variant) && in_array($variant, ['revisao', 'privacidade', 'vazio', 'falha'], true) ? $variant : 'completa';
        $grants = match ($variant) {
            'revisao' => ['usuarios.validar', 'eventos.aprovar'],
            'privacidade' => ['lgpd.visualizar'],
            default => array_values(array_filter([...array_column(self::PAGES, 1), 'padrinhos.validar_conclusao', 'lgpd.responder'])),
        };
        $admin = $context === 'administrador';
        $pages = array_filter(self::PAGES, fn ($meta) => $meta[1] === null || in_array($meta[1], $grants, true));
        $allowed = $admin && isset($pages[$page]);
        $params = array_filter(['contexto' => 'administrador', 'visao' => $variant === 'completa' ? null : $variant]);
        $queues = $admin && ! in_array($variant, ['vazio', 'falha'], true) ? array_intersect_key(self::queues(), $pages) : [];
        if (! in_array('padrinhos.validar_conclusao', $grants, true) && isset($queues['candidatos'])) {
            $queues['candidatos'] = array_values(array_filter($queues['candidatos'], fn ($row) => $row['kind'] !== 'padrinho'));
        }

        if (! $allowed) {
            return response()->view('admin.restrito', compact('page', 'pages', 'variant', 'admin', 'params', 'queues') + ['title' => 'Acesso restrito'], 403);
        }

        return response()->view('admin.'.$page, compact('page', 'pages', 'variant', 'admin', 'params', 'queues')
            + ['title' => self::PAGES[$page][0], 'records' => $queues[$page] ?? []]);
    }

    public const RESULTS = [
        'fila-eventos' => ['publicado', 'reprovado'],
        'cadastros-ong' => ['aguardando aprovação', 'ativo', 'reprovado'],
        'upgrades-org' => ['em_analise', 'aprovada', 'reprovada'],
        'candidatos' => ['em_analise', 'correcao_solicitada', 'aprovado', 'reprovado', 'reprovado_definitivamente', 'aprovada', 'rejeitada'],
        'solicitacoes-lgpd' => ['em_analise', 'concluida', 'recusada'],
    ];

    public const PENDING = ['aguardando a confirmação', 'aguardando aprovação', 'aguardando_validacao', 'pendente', 'em_analise', 'renovacao'];

    public static function pendingCount(array $rows): int
    {
        return count(array_filter($rows, fn ($row) => $row['id'] !== SponsorshipFixtures::APPLICANT && in_array($row['status'], self::PENDING, true)));
    }

    private static function queues(): array
    {
        $name = PublicProfileFixtures::pessoa()['nome_user'];
        $ong1 = PublicProfileFixtures::ong(1)['usuario']['nome'];
        $ong2 = PublicProfileFixtures::ong(2)['usuario']['nome'];
        $events = [];
        foreach ([1 => 'publicado', 7 => 'pendente', 8 => 'reanalise', 9 => 'reprovado', 10 => 'cancelado', 14 => 'finalizado'] as $id => $scenario) {
            $fixture = VisualTwoFixtures::page('detail', $scenario, $id);
            $event = $fixture['evento'];
            $flow = VisualTwoFixtures::make($scenario, $id);
            $owner = VisualThreeFixtures::ownership($id);
            $events[] = self::record('evento-'.$id, $event->nm_evento, 'evento', $flow['event_status'] === 'ativo' ? 'publicado' : $flow['event_status'],
                $scenario === 'reanalise' ? 'Mudança em data e local. Novas candidaturas aguardam decisão.' : $event->descricao_evento,
                ['Entidade autora' => $owner['ong_id'] ? $ong1 : $name, 'Responsável' => $id === 7 ? 'Sara de Exemplo' : $name, 'Vagas informativas do Evento' => $event->vagas_evento ?? 'Sem informação'],
                ['agendas' => $flow['agendas'], 'reanalysis' => $flow['reanalysis'],
                    'category' => (($event->cat_evento_id ?? null) === 2 || in_array($id, [9, 10, 14], true)) ? 'Educação' : ($id === 1 ? 'Meio ambiente' : 'Apoio comunitário'),
                    'starts' => substr($flow['agendas'][0]['data_inicio'], 0, 10),
                    'ong' => $owner['ong_id'] ? 'ong-'.$owner['ong_id'] : null, 'public' => in_array($id, [1, 7], true) ? route('eventos.show', ['id' => $id, 'contexto' => 'administrador']) : null]);
        }

        $criteria = [
            'Cadastro e função' => 'Aprovado · voluntário', 'Participações confirmadas' => '5 (mínimo 5)',
            'Confiabilidade formada' => '90 (mínimo 80)', 'Média de avaliações' => '4,5 (mínimo 4,0)',
            'Bloqueio' => 'Nenhum', 'Apadrinhados ativos' => 'Nenhum',
            'Solicitação em aberto' => 'Somente este pedido', 'Intervalo desde a última reprovação' => 'Sem reprovação nos últimos 30 dias',
        ];

        return [
            'fila-eventos' => $events,
            'cadastros-ong' => [
                self::record('ong-31', 'Rede Comunitária de Exemplo', 'ong', 'aguardando aprovação', 'Organização de apoio comunitário em Guarulhos.', ['Dados públicos' => 'Nome institucional, causa e município', 'Contato cadastral privado' => 'rede.comunitaria@example.test']),
                self::record('ong-1', $ong1, 'ong', 'ativo', 'Organização com Eventos vinculados.', [], ['public' => route('ong.perfil', ['ong' => 1, 'contexto' => 'administrador'])]),
                self::record('ong-2', $ong2, 'ong', 'ativo', 'Cadastro aprovado.', [], ['public' => route('ong.perfil', ['ong' => 2, 'contexto' => 'administrador'])]),
                self::record('ong-32', 'Associação de Exemplo', 'ong', 'reprovado', 'Cadastro com decisão registrada.'),
            ],
            'candidatos' => [
                self::record(SponsorshipFixtures::APPLICANT, $name, 'pessoa', 'aguardando_validacao', 'Cadastro encaminhado para revisão cadastral e documental.', extra: ['documents' => ['pendente', 'pendente']]),
                self::record('pessoa-31', 'Pessoa de Exemplo 31', 'pessoa', 'aguardando_validacao', 'Maioridade verificada no cadastro.', extra: ['documents' => ['pendente', 'pendente']]),
                self::record('pessoa-32', 'Pessoa de Exemplo 32', 'pessoa', 'em_analise', 'Antecedentes encaminhados para análise humana.', ['Padrinho' => 'Aguardando aceite de pessoa elegível'], ['blocked' => 'A aprovação requer um padrinho elegível com aceite.', 'documents' => ['aprovado', 'aprovado']]),
                self::record('pessoa-33', 'Pessoa de Exemplo 33', 'pessoa', 'em_analise', 'Análise sob responsabilidade de outro administrador.', extra: ['lock' => 'outro', 'documents' => ['pendente', 'pendente'], 'unavailableDocuments' => [1]]),
                self::record('pessoa-34', 'Pessoa de Exemplo 34', 'pessoa', 'em_analise', 'A análise anterior expirou após 24 horas.', extra: ['lock' => 'expirado', 'documents' => ['aprovado', 'aprovado']]),
                self::record('pessoa-35', 'Pessoa de Exemplo 35', 'pessoa', 'correcao_solicitada', 'Aguardando correção e novo envio.', extra: ['reason' => 'O documento de identidade precisa estar legível.', 'documents' => ['rejeitado', 'aprovado']]),
                self::record('pessoa-36', 'Pessoa de Exemplo 36', 'pessoa', 'reprovado', 'Três reprovações em 30 dias. Novo envio bloqueado por sete dias.', extra: ['reason' => 'A certidão enviada não pôde ser validada.']),
                self::record('pessoa-37', 'Pessoa de Exemplo 37', 'pessoa', 'reprovado_definitivamente', 'Decisão administrativa registrada.'),
                self::record('pessoa-38', 'Pessoa de Exemplo 38', 'pessoa', 'aprovado', 'Antecedentes analisados com aceite e avaliação humana.', ['Padrinho' => 'Elegibilidade e aceite confirmados'], ['documents' => ['aprovado', 'aprovado']]),
                self::record('renovacao-39', 'Pessoa de Exemplo 39', 'renovacao', 'renovacao', 'Certidão a renovar após 12 meses. Cadastro permanece aprovado.'),
                self::record('padrinho-38', 'Conclusão de acompanhamento · Pessoa de Exemplo 38', 'padrinho', 'pendente', 'Pedido de encerramento do acompanhamento.', ['Acompanhamento' => '6 meses', 'Participações confirmadas' => '5']),
            ],
            'upgrades-org' => [
                self::record('promocao-1', $name, 'promocao', 'pendente', 'Quero organizar atividades de apoio à comunidade.', $criteria),
                self::record('promocao-41', 'Pessoa de Exemplo 41', 'promocao', 'em_analise', 'Quero coordenar as próximas ações de leitura.', array_replace($criteria, ['Bloqueio' => 'Ativo']), ['blocked' => 'O cadastro tem um bloqueio ativo.']),
                self::record('promocao-42', 'Pessoa de Exemplo 42', 'promocao', 'aprovada', 'Quero organizar atividades locais.'),
                self::record('promocao-43', 'Pessoa de Exemplo 43', 'promocao', 'reprovada', 'Quero organizar um mutirão.', extra: ['reason' => 'Participações abaixo do mínimo. Nova solicitação após 30 dias.']),
            ],
            'denuncias' => [
                self::record('ocorrencia-601', 'Desistência tardia · '.$events[0]['title'], 'ocorrencia', 'historico', 'Desistência a menos de 24 horas da Agenda.', ['Agenda' => '#101', 'Inscrição' => '#601', 'Participante' => $name, 'Revisão' => 'Sem decisão administrativa disponível']),
                self::record('ocorrencia-1001', 'Cancelamento · '.$events[4]['title'], 'ocorrencia', 'historico', 'Agenda cancelada pelo responsável. A ocorrência não altera a confiabilidade de quem se inscreveu.', ['Agenda' => '#1001']),
            ],
            'solicitacoes-lgpd' => [
                self::record('lgpd-51', 'Acesso a dados', 'lgpd', 'pendente', 'Pessoa de Exemplo 51 solicita acesso aos dados do cadastro.'),
                self::record('lgpd-52', 'Correção de dados', 'lgpd', 'em_analise', 'Pessoa de Exemplo 52 solicita atualização cadastral.'),
                self::record('lgpd-53', 'Informações sobre tratamento', 'lgpd', 'concluida', 'Pedido anterior com resposta registrada.', extra: ['reason' => 'Orientações sobre o tratamento de dados enviadas à pessoa titular.']),
                self::record('lgpd-54', 'Portabilidade', 'lgpd', 'recusada', 'Pedido anterior encerrado.', extra: ['reason' => 'O pedido precisa ser apresentado pela pessoa titular.']),
            ],
        ];
    }

    private static function record(string $id, string $title, string $kind, string $status, string $description, array $facts = [], array $extra = []): array
    {
        return compact('id', 'title', 'kind', 'status', 'description', 'facts') + $extra;
    }
}
