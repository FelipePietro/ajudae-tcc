<?php

use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
    return view('teste');
});

Route::get('/cadastro-ong', function () {
    return view('cadastro_ong');
})->name('ong.cadastrar.form');
 
// Endpoint chamado via fetch() pelo JS quando o usuário termina de digitar o CNPJ.
// Em produção, isso consultaria a Receita Federal (ex: via BrasilAPI ou ReceitaWS).
Route::get('/api/ong/consultar-cnpj/{cnpj}', function (string $cnpj) {
 
    $cnpj = preg_replace('/\D/', '', $cnpj);
 
    if (strlen($cnpj) !== 14) {
        return response()->json(['message' => 'CNPJ inválido'], 422);
    }
 
    // TODO: trocar pela chamada real à API de CNPJ.
    // Exemplo com BrasilAPI:
    // $resposta = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
    // if ($resposta->failed()) return response()->json(['message' => 'CNPJ não encontrado'], 404);
    // $dados = $resposta->json();
 
    $dadosSimulados = [
        'razao_social'  => 'Instituto Exemplo Cadastrado',
        'nome_fantasia' => 'Exemplo Cadastrado',
        'cidade'        => 'São Paulo',
        'estado'        => 'SP',
        'situacao'      => 'ATIVA',
    ];
 
    if ($dadosSimulados['situacao'] !== 'ATIVA') {
        return response()->json(['message' => 'CNPJ inativo ou com natureza jurídica incompatível'], 422);
    }
 
    return response()->json($dadosSimulados);
})->name('ong.consultar-cnpj');
 
// Recebe o formulário de cadastro
Route::post('/ong/cadastrar', function (Request $request) {
 
    $dados = $request->validate([
        'cnpj'          => ['required', 'string', 'size:18'], // já vem mascarado: 00.000.000/0001-00
        'razao_social'  => ['required', 'string', 'max:255'],
        'nome_fantasia' => ['required', 'string', 'max:255'],
        'cidade'        => ['required', 'string', 'max:120'],
        'estado'        => ['required', 'string', 'size:2'],
        'email'         => ['required', 'email', 'max:255', 'unique:ongs,email'],
        'telefone'      => ['required', 'string', 'max:15'],
        'senha'         => ['required', 'string', 'min:8', 'confirmed'],
        'descricao'     => ['required', 'string', 'max:2000'],
        'logo'          => ['nullable', 'image', 'max:5120'], // 5MB
        'aceite_termo_responsabilidade' => ['accepted'],
        'aceite_privacidade'            => ['accepted'],
    ], [
        'aceite_termo_responsabilidade.accepted' => 'É necessário aceitar o Termo de Responsabilidade da ONG.',
        'aceite_privacidade.accepted'            => 'É necessário concordar com a Política de Privacidade.',
    ]);
 
    // Aqui entraria a criação real do registro, algo como:
    // $ong = Ong::create([
    //     'cnpj'          => $dados['cnpj'],
    //     'razao_social'  => $dados['razao_social'],
    //     'nome_fantasia' => $dados['nome_fantasia'],
    //     'cidade'        => $dados['cidade'],
    //     'estado'        => $dados['estado'],
    //     'email'         => $dados['email'],
    //     'telefone'      => $dados['telefone'],
    //     'password'      => bcrypt($dados['senha']),
    //     'descricao'     => $dados['descricao'],
    //     'status'        => 'aguardando_aprovacao',
    // ]);
    // if ($request->hasFile('logo')) {
    //     $ong->logo_path = $request->file('logo')->store('logos-ong', 'public');
    //     $ong->save();
    // }
 
    return redirect()
        ->route('ong.cadastrar.form')
        ->with('sucesso', 'Cadastro enviado! Você receberá um e-mail assim que a análise for concluída.');
})->name('ong.cadastrar');

Route::get('/painel-ong', function () {
    $ong = [
        'icone'          => '🌱',
        'nome'           => 'Instituto Esperança Social',
        'nome_fantasia'  => 'Esperança Social',
        'cidade'         => 'São Paulo',
        'estado'         => 'SP',
        'cnpj'           => '11.222.333/0001-81',
        'area_atuacao'   => 'Assistência social · Educação',
        'ativa'          => true,
        'verificada'     => true,
        'site'           => 'esperancasocial.org.br',
        'aprovado_em'    => '12 jan. 2024',
    ];
 
    $eventos = [
        [
            'icone' => '🌿',
            'nome' => 'Limpeza de Parques',
            'local' => 'Horto Florestal',
            'data' => '15 maio 2025',
            'responsavel' => 'Carlos F.',
            'cargo' => 'Organizador',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '8 pend.',
        ],
        [
            'icone' => '👕',
            'nome' => 'Doação de Roupas',
            'local' => 'Brás, SP',
            'data' => '20 maio 2025',
            'responsavel' => 'Maria L.',
            'cargo' => 'Organizadora',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '15 pend.',
        ],
        [
            'icone' => '💉',
            'nome' => 'Campanha de Vacinação',
            'local' => 'Centro, SP',
            'data' => '28 maio 2025',
            'responsavel' => null,
            'cargo' => null,
            'status' => 'Aguardando aprovação',
            'status_class' => 'status-waiting',
            'candidatos' => '0 pend.',
        ],
        [
            'icone' => '📚',
            'nome' => 'Aula de Reforço Escolar',
            'local' => 'Cidade Tiradentes',
            'data' => '05 jun 2025',
            'responsavel' => 'Carlos F.',
            'cargo' => 'Organizador',
            'status' => 'Publicado',
            'status_class' => 'status-published',
            'candidatos' => '4 pend.',
        ],
    ];
 
    $funcionarios = [
        [
            'iniciais' => 'CF',
            'cor'      => 'green',
            'nome'     => 'Carlos Ferreira',
            'meta'     => '2 eventos designados · Organizador principal',
            'status'   => 'accepted',
        ],
        [
            'iniciais' => 'ML',
            'cor'      => 'yellow',
            'nome'     => 'Maria Lima',
            'meta'     => '1 evento designado',
            'status'   => 'accepted',
        ],
        [
            'iniciais' => 'PS',
            'cor'      => 'blue',
            'nome'     => 'Pedro Santos',
            'meta'     => 'Nenhum evento · convite aguardando resposta',
            'status'   => 'pending',
        ],
    ];
 
    $atividades = [
        [
            'cor'   => 'green',
            'texto' => 'Carlos aprovou candidatura de Ana Costa para <strong>Limpeza de Parques</strong>',
            'tempo' => 'hoje, 14:32',
        ],
        [
            'cor'   => 'orange',
            'texto' => 'Evento <strong>Campanha de Vacinação</strong> enviado para aprovação',
            'tempo' => 'ontem, 09:15',
        ],
        [
            'cor'   => 'blue',
            'texto' => 'Pedro Santos convidado para a equipe via e-mail',
            'tempo' => 'há 2 dias',
        ],
        [
            'cor'   => 'green',
            'texto' => '<strong>Doação de Roupas</strong> publicado — 15 candidaturas recebidas',
            'tempo' => 'há 3 dias',
        ],
    ];
 
    $stats = [
        'eventos_ativos'      => collect($eventos)->where('status_class', 'status-published')->count(),
        'funcionarios'        => count($funcionarios),
        'total_voluntarios'   => 128,
        'eventos_realizados'  => 32,
        'horas_doadas'        => '2.580',
    ];
 
    return view('painel_ong', compact('ong', 'eventos', 'funcionarios', 'atividades', 'stats'));
})->name('dashboard');

Route::get('/eventos', function () {
    return redirect()->route('dashboard');
})->name('eventos.index');

Route::get('/candidatos', function () {
    return redirect()->route('dashboard');
})->name('candidatos.index');

Route::get('/editar-ong', function () {
    $ong = [
        'id'            => 4821,
        'cnpj'          => '12.345.678/0001-90',
        'razao_social'  => 'Instituto Mãos que Ajudam',
        'nome_fantasia' => 'Mãos que Ajudam',
        'cidade'        => 'Guarulhos',
        'estado'        => 'SP',
        'email'         => 'contato@maosqueajudam.org.br',
        'telefone'      => '(11) 3000-4521',
        'descricao'     => 'Atuamos há 8 anos apoiando famílias em situação de vulnerabilidade na região de Guarulhos, com foco em segurança alimentar, reforço escolar e capacitação profissional para jovens e adultos.',
        'logo_url'      => 'https://ajudae.org/logo-atual.png',
        'verificada'    => true,
        'ativa_desde'   => '12/03/2024',
    ];
 
    return view('editar_ong', compact('ong'));
})->name('perfil-ong.editar');

Route::put('/perfil-ong/{id}', function (Request $request, $id) {
 
    $dados = $request->validate([
        'razao_social'  => ['required', 'string', 'max:255'],
        'nome_fantasia' => ['required', 'string', 'max:255'],
        'cidade'        => ['required', 'string', 'max:120'],
        'estado'        => ['required', 'string', 'size:2'],
        'email'         => ['required', 'email', 'max:255'],
        'telefone'      => ['nullable', 'string', 'max:20'],
        'descricao'     => ['required', 'string', 'max:2000'],
        'senha'         => ['nullable', 'string', 'min:8', 'confirmed'],
        'logo'          => ['nullable', 'image', 'max:5120'], // 5MB
        'remover_logo'  => ['nullable', 'boolean'],
    ]);
 
    return redirect()
        ->route('perfil-ong.editar')
        ->with('sucesso', 'Perfil da ONG atualizado com sucesso.');
})->name('perfil-ong.atualizar');

Route::get('/perfil-ong', function () {
    $usuario = [
        'iniciais'     => 'CF',
        'nome'         => 'Carlos Ferreira',
        'cidade'       => 'São Paulo',
        'estado'       => 'SP',
        'tipo'         => 'Organizador independente',
        'membro_desde' => 'jan. 2024',
        'verificado'   => true,
        'papel'        => 'Organizador',
        'areas'        => ['Meio Ambiente', 'Educação'],
        'email'        => 'carlos@email.com',
        'telefone'     => '(11) 99999-0000',
    ];
 
    $eventosCriados = [
        [
            'icone' => '🌿',
            'nome'  => 'Mutirão de Plantio — Instituto Verde',
            'local' => 'Parque da Cidade, São Paulo - SP',
            'data'  => '12 de setembro de 2026',
            'vagas' => '15 vagas restantes',
        ],
    ];
 
    $stats = [
        'eventos_organizados'     => count($eventosCriados) + 13, // exemplo: total histórico, não só os exibidos
        'voluntarios_gerenciados' => 87,
        'badges'                  => 8,
    ];
 
    return view('perfil_ong', compact('usuario', 'eventosCriados', 'stats'));
})->name('perfil');

Route::get('/notificacoes', function () {
    return redirect()->route('dashboard');
})->name('notificacoes');

Route::get('/configuracoes', function () {
    return redirect()->route('dashboard');
})->name('configuracoes');