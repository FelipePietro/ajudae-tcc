<?php

namespace App\Providers;

use App\Models\Agenda;
use App\Models\Evento;
use App\Policies\AgendaPolicy;
use App\Policies\EventoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Evento::class, EventoPolicy::class);
        Gate::policy(Agenda::class, AgendaPolicy::class);
    }
}
