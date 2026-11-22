<?php

use Illuminate\Contracts\Console\Kernel;

// Synthetic, local template rendering only. No API, authentication, database or certificate service.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$certificate = (object) [
    'nome_participante' => 'Pessoa de Exemplo',
    'nome_evento' => 'Ação comunitária de educação',
    'total_minutos' => 120,
    'nome_responsavel' => 'Organização de Exemplo',
    'emitido_em' => new DateTimeImmutable('2026-11-19'),
    'codigo_verificacao' => 'AJD-'.str_repeat('A', 32),
];
$html = view('certificados.documento', ['certificado' => $certificate])->render();
$pdf = new Dompdf\Dompdf;
$pdf->set_option('isRemoteEnabled', false);
$pdf->loadHtml($html);
$pdf->render();
$directory = __DIR__.'/../docs/evidencias/bloco-visual-6-4/pdf';
if (! is_dir($directory)) {
    mkdir($directory, 0755, true);
}
file_put_contents($directory.'/certificado-template.pdf', $pdf->output());
echo "Rendered synthetic Blade certificate.\n";
