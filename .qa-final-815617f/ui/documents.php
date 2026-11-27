<?php

require __DIR__.'/guard.php';

use App\Models\DocumentoPessoa;
use App\Models\Permissao;
use App\Models\Pessoa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$fixture = json_decode(file_get_contents(__DIR__.'/fixture-session.json'), true, flags: JSON_THROW_ON_ERROR);
$pdf = file_get_contents(base_path('resources/fixtures/admin-documents/certidao.pdf'));
$data = DB::transaction(static function () use ($fixture, $pdf) {
    $admin = Pessoa::findOrFail($fixture['person'])->replicate();
    $admin->forceFill([
        'nm_pessoa' => 'Segundo analista sintético',
        'login_pessoa' => 'qa.luiz.revisor2', 'email_pessoa' => 'qa.luiz.revisor2@example.test',
        'cpf_pessoa' => '90100000014', 'rg_pessoa' => '90100014', 'role_pessoa' => 'administrador',
    ])->save();
    $admin->permissoes()->sync([Permissao::firstOrCreate(['nome' => 'usuarios.validar'])->getKey()]);
    $make = static function ($who, $type, array $attributes = []) use ($pdf) {
        $path = 'documentos/'.bin2hex(random_bytes(16)).'.pdf';
        if (!Storage::disk('local')->put($path, $pdf)) {
            throw new RuntimeException('Falha ao preparar arquivo sintetico');
        }
        return DocumentoPessoa::create([
            'pessoa_id' => $who, 'tipo' => $type, 'status' => 'pendente', 'ativo' => true,
            'uploaded_at' => now(), 'storage_disk' => 'local', 'storage_path' => $path,
            'mime_type' => 'application/pdf', 'size_bytes' => strlen($pdf), ...$attributes,
        ])->getKey();
    };
    return [
        'registration' => $make($fixture['pendingPerson'], 'documento_identidade'),
        'registrationCertificate' => $make($fixture['pendingPerson'], 'antecedentes_criminais'),
        'renewal' => $make($fixture['person'], 'antecedentes_criminais'),
        'expired' => $make($fixture['other'], 'antecedentes_criminais', ['delete_after' => now()->subDay()]),
        'historical' => $make($fixture['person'], 'antecedentes_criminais', ['ativo' => false]),
        'identity' => $make($fixture['person'], 'documento_identidade'),
    ];
});
file_put_contents(__DIR__.'/doc-fixtures.json', json_encode([
    ...$data, 'pdfBytes' => strlen($pdf), 'pdfHash' => hash('sha256', $pdf),
], JSON_THROW_ON_ERROR));
echo "Documentos sinteticos em disco privado local.\n";
