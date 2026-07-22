<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

/** Configuración transversal aplicada durante el arranque de Laravel. */
class AppServiceProvider extends ServiceProvider
{
    /** Activa paginación Bootstrap y obliga HTTPS cuando el entorno es producción. */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // En desarrollo XAMPP puede usar HTTP; producción debe generar enlaces HTTPS.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
