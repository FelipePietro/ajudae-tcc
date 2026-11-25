<?php

use App\Http\Controllers\WebEventManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestao')->name('identity.events.')->group(function (): void {
    Route::get('/eventos', [WebEventManagementController::class, 'index'])->name('index')->block();
    Route::get('/eventos/catalogos', [WebEventManagementController::class, 'catalogos'])->name('catalogs')->block();
    Route::post('/eventos', [WebEventManagementController::class, 'store'])->name('store')->block();
    Route::get('/eventos/{evento}', [WebEventManagementController::class, 'show'])->whereNumber('evento')->name('show')->block();
    Route::patch('/eventos/{evento}', [WebEventManagementController::class, 'update'])->whereNumber('evento')->name('update')->block();
    Route::post('/eventos/{evento}/cancelar', [WebEventManagementController::class, 'cancelar'])->whereNumber('evento')->name('cancel')->block();
    Route::get('/eventos/{evento}/imagem', [WebEventManagementController::class, 'showImage'])->whereNumber('evento')->name('image.show')->block();
    Route::post('/eventos/{evento}/imagem', [WebEventManagementController::class, 'uploadImage'])->whereNumber('evento')->name('image.upload')->block();
    Route::delete('/eventos/{evento}/imagem', [WebEventManagementController::class, 'removeImage'])->whereNumber('evento')->name('image.remove')->block();
    Route::post('/eventos/{evento}/agendas', [WebEventManagementController::class, 'storeAgenda'])->whereNumber('evento')->name('agendas.store')->block();
    Route::patch('/agendas/{agenda}', [WebEventManagementController::class, 'updateAgenda'])->whereNumber('agenda')->name('agendas.update')->block();
    Route::delete('/agendas/{agenda}', [WebEventManagementController::class, 'destroyAgenda'])->whereNumber('agenda')->name('agendas.destroy')->block();
    Route::post('/agendas/{agenda}/cancelar', [WebEventManagementController::class, 'cancelarAgenda'])->whereNumber('agenda')->name('agendas.cancel')->block();
});
