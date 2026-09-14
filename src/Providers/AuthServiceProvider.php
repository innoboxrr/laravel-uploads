<?php

namespace Innoboxrr\LaravelUploads\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Hereda de ServiceProvider y no del AuthServiceProvider de Foundation, igual
 * que los proveedores de rutas y de eventos: del de Foundation no se usaba
 * nada y cada politica se registra aqui con Gate::policy().
 */
class AuthServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->mapPolicies();
    }

    /**
     * Policies/{Modelo}Policy.php => Models/{Modelo}.php.
     */
    public function mapPolicies(): void
    {
        foreach (glob(__DIR__ . '/../Policies/*.php') ?: [] as $file) {

            $name = basename($file, '.php');

            $policy = 'Innoboxrr\LaravelUploads\Policies\\' . $name;

            // El modelo se arma con el nombre corto de la policy. Antes se armaba
            // con su nombre completo, la clase nunca existia y no se registraba
            // ninguna: solo funcionaban por el adivinador de nombres de Laravel.
            $model = 'Innoboxrr\LaravelUploads\Models\\' . substr($name, 0, -strlen('Policy'));

            if (class_exists($model) && class_exists($policy)) {
                Gate::policy($model, $policy);
            }

        }
    }

}
