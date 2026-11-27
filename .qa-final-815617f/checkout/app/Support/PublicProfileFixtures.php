<?php

namespace App\Support;

/** Original HEAD profile presentation fixtures, never API data. */
final class PublicProfileFixtures
{
    public static function selectedPerson(int $id): ?array
    {
        $directory = [1 => ['Lucas Pereira', 'Organizador', 1000], 2 => ['Sara de Exemplo', 'Organizador', 600], 3 => ['Rafael de Exemplo', 'Organizador', 350], 11 => ['Alex de Exemplo', 'Voluntário', 150], 12 => ['Participante de Exemplo', 'Voluntário', 300], 13 => ['Voluntário de Exemplo', 'Voluntário', 450]];
        if (! isset($directory[$id])) {
            return null;
        }
        $data = self::pessoa();
        [$data['nome_user'],$data['tipo_user'],$data['xp_user']] = $directory[$id];
        $data['id_user'] = $id;
        if ($id !== 1) {
            $data['bio_user'] = '';
            $data['cidade_user'] = 'Localidade não informada';
            $data['uf_user'] = '';
            foreach (['habilidades_user', 'recursos_user', 'causas_user', 'badges', 'historico', 'certificados'] as $key) {
                $data[$key] = [];
            }
            $data['badges_user'] = 0;
            $data['eventos_user'] = 0;
            $data['posicao_user'] = '—';
            $data['nivel_user'] = 1;
            $data['titulo_nivel'] = 'Iniciante';
        }

        return self::withLevel($data);
    }

    /** Approved V1 thresholds; presentation only, never a persisted XP balance. */
    private static function withLevel(array $data): array
    {
        $levels = [[0, 'Iniciante'], [500, 'Colaborador'], [1500, 'Engajado'], [3500, 'Agente de Impacto'], [7000, 'Transformador']];
        $index = 0;
        foreach ($levels as $i => [$threshold]) {
            if ($data['xp_user'] >= $threshold) {
                $index = $i;
            }
        }
        $next = $levels[$index + 1] ?? null;
        $data['nivel_user'] = $index + 1;
        $data['titulo_nivel'] = $levels[$index][1];
        $data['xp_inicio_nivel'] = $levels[$index][0];
        $data['xp_proximo_nivel'] = $next[0] ?? $levels[$index][0];
        $data['titulo_proximo_nivel'] = $next[1] ?? null;

        return $data;
    }

    /** Only named internal origins and verified IDs; never an arbitrary redirect URL. */
    public static function personReturn(int $id, string $context): ?array
    {
        $params = ['contexto' => $context, 'jornada' => VisualThreeFixtures::scenario($context), 'convites' => implode(',', VisualThreeFixtures::invitationIds()) ?: null];
        $origin = request()->query('origem');
        $event = request()->query('evento_origem');
        if ($origin === 'organizadores' && $context === 'ong' && in_array($id, [1, 2, 3], true)) {
            return ['href' => route('ong.organizadores', $params), 'label' => 'Retornar aos organizadores'];
        }
        if (is_string($event) && preg_match('/^[1-9][0-9]*$/D', $event)) {
            $eventId = (int) $event;
            if ($origin === 'evento' && (VisualThreeFixtures::ownership($eventId)['pessoa_id'] ?? null) === $id) {
                return ['href' => route('eventos.show', ['id' => $eventId, 'contexto' => $context]), 'label' => 'Retornar ao evento'];
            }
            if ($origin === 'candidatos' && in_array($id, [11, 12, 13], true) && VisualThreeFixtures::canManage($eventId, $context)) {
                return ['href' => route('ong.candidatos', array_merge($params, ['evento' => $eventId])), 'label' => 'Retornar às candidaturas'];
            }
        }

        return null;
    }

    public static function pessoa(): array
    {

        return self::withLevel([

            'id_user' => 1,

            'nome_user' => 'Lucas Pereira',

            'foto_user' => '/images/avatar-pessoa.svg',

            'cidade_user' => 'São Paulo',

            'uf_user' => 'SP',

            'tipo_user' => 'Voluntário',

            'bio_user' => 'Apaixonado por causas ambientais e educação. Busco contribuir com a comunidade e aprender com cada experiência. Acredito que pequenas ações coletivas geram transformações reais.',

            /*
            |--------------------------------------------------------------------------
            | Gamificação
            |--------------------------------------------------------------------------
            */

            'xp_user' => 1000,

            'badges_user' => 3,

            'eventos_user' => 8,

            'posicao_user' => 67,

            /*
            |--------------------------------------------------------------------------
            | Habilidades
            |--------------------------------------------------------------------------
            */

            'habilidades_user' => [

                [
                    'id' => 1,
                    'nome' => 'Trabalho em equipe',
                    'nivel' => 4,
                ],

                [
                    'id' => 2,
                    'nome' => 'Comunicação',
                    'nivel' => 3,
                ],

                [
                    'id' => 3,
                    'nome' => 'Jardinagem',
                    'nivel' => 2,
                ],

                [
                    'id' => 4,
                    'nome' => 'Liderança',
                    'nivel' => 3,
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Recursos
            |--------------------------------------------------------------------------
            */

            'recursos_user' => [

                [
                    'id' => 1,
                    'nome' => 'Veículo',
                    'detalhes' => 'Disponível aos finais de semana',
                ],

                [
                    'id' => 2,
                    'nome' => 'Notebook',
                    'detalhes' => 'Notebook próprio',
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Causas
            |--------------------------------------------------------------------------
            */

            'causas_user' => [

                [
                    'id' => 1,
                    'nome' => 'Meio Ambiente',
                    'icone' => '🌱',
                ],

                [
                    'id' => 2,
                    'nome' => 'Educação',
                    'icone' => '📚',
                ],

                [
                    'id' => 3,
                    'nome' => 'Saúde',
                    'icone' => '❤️',
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Ranking
            |--------------------------------------------------------------------------
            */

            'ranking' => [

                [
                    'posicao' => 1,
                    'nome' => 'Ana Costa',
                    'xp' => 1200,
                ],

                [
                    'posicao' => 2,
                    'nome' => 'Bruno Melo',
                    'xp' => 1100,
                ],

                [
                    'posicao' => 3,
                    'nome' => 'Carlos F.',
                    'xp' => 1050,
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Badges
            |--------------------------------------------------------------------------
            */

            'badges' => [

                [
                    'id' => 1,
                    'nome' => 'Primeiro Passo',
                    'descricao' => 'Participou do primeiro evento.',
                    'icone' => '🌱',
                ],

                [
                    'id' => 2,
                    'nome' => 'Voluntário Ativo',
                    'descricao' => 'Participou de 5 eventos.',
                    'icone' => '🏅',
                ],

                [
                    'id' => 3,
                    'nome' => 'Amigo da Natureza',
                    'descricao' => 'Participou de eventos ambientais.',
                    'icone' => '🌳',
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Histórico
            |--------------------------------------------------------------------------
            */

            'historico' => [

                [
                    'id' => 1,
                    'nome_evento' => 'Limpeza no Parque',
                    'data' => '05/04/2026',
                    'status' => 'Concluído',
                    'xp_recebido' => 100,
                ],

                [
                    'id' => 2,
                    'nome_evento' => 'Aulas de Reforço',
                    'data' => '19/04/2026',
                    'status' => 'Concluído',
                    'xp_recebido' => 80,
                ],

                [
                    'id' => 3,
                    'nome_evento' => 'Campanha de Arrecadação',
                    'data' => '10/05/2026',
                    'status' => 'Concluído',
                    'xp_recebido' => 120,
                ],

            ],

            /*
            |--------------------------------------------------------------------------
            | Certificados
            |--------------------------------------------------------------------------
            */

            'certificados' => [

                [
                    'id' => 1,
                    'nome_evento' => 'Limpeza no Parque',
                    'data' => '05 abr. 2026',
                ],

                [
                    'id' => 2,
                    'nome_evento' => 'Aulas de Reforço',
                    'data' => '19 abr. 2026',
                ],

            ],

        ]);

    }

    public static function ong(int $id = 1): array
    {

        $usuario = [

            'iniciais' => 'IE',

            'nome' => 'Instituto Esperança',

            'cidade' => 'São Paulo',

            'estado' => 'SP',

            'tipo' => 'ONG',

            'membro_desde' => 'jan. 2024',

            'verificado' => true,

            'papel' => 'Organização',

            'areas' => [
                'Meio Ambiente',
                'Educação',
            ],

            'email' => 'responsavel@example.test',

            'telefone' => '00000000000',

        ];

        $eventosCriados = [

            [
                'icone' => '🌿',

                'nome' => 'Mutirão de Plantio — Instituto Verde',

                'local' => 'Parque da Cidade, São Paulo - SP',

                'data' => '12 de setembro de 2026',

                'vagas' => '15 vagas restantes',
            ],

        ];

        $stats = [

            'eventos_organizados' => count($eventosCriados) + 13,

            'voluntarios_gerenciados' => 87,

            'badges' => 8,

        ];

        // Synthetic public identities; selected ONG is independent of the browsing account.
        $usuario['ong_id'] = $id;
        unset($usuario['email'], $usuario['telefone']);
        $eventosCriados[0]['evento_id'] = $id;
        if ($id === 2) {
            $usuario['nome'] = 'Instituto Verde';
            $usuario['iniciais'] = 'IV';
            $usuario['cidade'] = 'Guarulhos';
            $usuario['areas'] = ['Meio Ambiente'];
        }
        $eventosCriados[0]['nome'] = $id === 2 ? 'Plantio comunitário' : 'Limpeza de Parques';

        return compact('usuario', 'eventosCriados', 'stats');

    }
}
