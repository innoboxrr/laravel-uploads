<?php

namespace Innoboxrr\LaravelUploads\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Innoboxrr\LaravelUploads\Tests\Fixtures\User;

/**
 * La aplicacion que instala el paquete: Sanctum para la sesion del SPA,
 * Intervention para comprimir imagenes, una tabla users y discos falsos.
 *
 * Los proveedores de dependencias se agregan aqui porque Testbench no hace
 * package discovery; en una aplicacion real los descubre Laravel.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return array_values(array_filter([
            'Laravel\Sanctum\SanctumServiceProvider',
            'Intervention\Image\Laravel\ServiceProvider',
            ...parent::getPackageProviders($app),
        ], 'class_exists'));
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('laravel-uploads.user_class', User::class);
        $app['config']->set('laravel-uploads.disk', 'public');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->app->make('migrator')->path(__DIR__ . '/database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');
    }

    /**
     * La sesion del guard sanctum, como la de un SPA con cookie. No se usa
     * Sanctum::actingAs() porque exige HasApiTokens, que un usuario de sesion
     * no necesita.
     */
    protected function signIn($user)
    {
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    protected function makeUser(array $attributes = []): User
    {
        static $sequence = 0;
        $sequence++;

        return User::create($attributes + [
            'name' => "Usuario {$sequence}",
            'email' => "usuario{$sequence}@example.com",
            'password' => 'secret',
        ]);
    }
}
