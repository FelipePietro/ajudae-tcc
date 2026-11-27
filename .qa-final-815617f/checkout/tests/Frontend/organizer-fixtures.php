<?php

use App\Support\VisualThreeFixtures;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

// Read-only test fixture export. Runs without a database or HTTP integration.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$states = [];
foreach (['elegivel' => 'pessoa', 'convite-pendente' => 'organizador', 'convite-bloqueado' => 'organizador', 'vinculado' => 'organizador-vinculado', 'ong-vinculos' => 'ong', 'vinculo-livre' => 'organizador-vinculado'] as $scenario => $context) {
    $app->instance('request', Request::create('/organizador', 'GET', ['jornada' => $scenario]));
    $states[$scenario] = VisualThreeFixtures::make($context);
}
echo json_encode($states, JSON_THROW_ON_ERROR);
