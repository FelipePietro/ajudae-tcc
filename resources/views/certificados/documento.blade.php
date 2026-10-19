<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 42px; }
        body { font-family: DejaVu Sans, sans-serif; color: #173042; }
        .page { border: 2px solid #1e6474; padding: 44px 38px; min-height: 650px; text-align: center; }
        .brand { font-size: 21px; font-weight: bold; letter-spacing: 2px; color: #176678; }
        h1 { font-size: 28px; margin: 52px 0 34px; }
        p { font-size: 15px; line-height: 1.8; }
        .name { font-size: 22px; font-weight: bold; margin: 24px 0; }
        .event { font-weight: bold; }
        .meta { margin-top: 60px; font-size: 12px; color: #355362; }
        .code { font-family: DejaVu Sans Mono, monospace; font-size: 11px; }
    </style>
</head>
<body>
<div class="page">
    <div class="brand">AJUDAÊ</div>
    <h1>Certificado de participação voluntária</h1>
    <p>Certificamos que</p>
    <div class="name">{{ $certificado->nome_participante }}</div>
    <p>participou do evento <span class="event">{{ $certificado->nome_evento }}</span>,<br>
        com carga horária total de {{ number_format($certificado->total_minutos / 60, 2, ',', '.') }} horas
        ({{ $certificado->total_minutos }} minutos).</p>
    <p>Responsável: {{ $certificado->nome_responsavel }}</p>
    <div class="meta">
        <p>Emitido em {{ $certificado->emitido_em->format('d/m/Y') }}</p>
        <p class="code">Código de verificação: {{ $certificado->codigo_verificacao }}</p>
        <p>Autenticidade verificável pela API pública do Ajudaê mediante este código.</p>
    </div>
</div>
</body>
</html>
