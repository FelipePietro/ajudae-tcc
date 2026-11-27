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
        if (config('identity.enabled')) {
            config([
                'session.http_only' => true,
                'session.same_site' => 'lax',
                'session.domain' => null,
                'session.path' => '/',
                'session.expire_on_close' => true,
                'session.cookie' => config('session.cookie').'_auth',
                'session.secure' => $this->app->isProduction() || config('session.secure'),
            ]);
        }

        Gate::policy(Evento::class, EventoPolicy::class);
        Gate::policy(Agenda::class, AgendaPolicy::class);
    }
}
