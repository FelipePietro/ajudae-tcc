<?php

use App\Http\Controllers\Api\AssinaturaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/assinaturas')->group(function () {
    Route::post('/', [AssinaturaController::class, 'store']);
    Route::get('/{id}', [AssinaturaController::class, 'show']);
});
