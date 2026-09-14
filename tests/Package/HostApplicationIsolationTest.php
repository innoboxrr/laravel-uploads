<?php

namespace Innoboxrr\LaravelUploads\Tests\Package;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as AppEventServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as AppRouteServiceProvider;
use Illuminate\Support\Facades\Route;
use Innoboxrr\LaravelUploads\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Lo que el paquete no debe hacerle a la aplicacion que lo instala.
 *
 * Se arma como bootstrap/app.php en Laravel 13: withRouting() deja el cargador
 * de rutas de la aplicacion en la clase base de Foundation y registra ese
 * proveedor, y withEvents(), que Application::configure() llama siempre,
 * registra el EventServiceProvider base. Asi se cuenta lo que el paquete agrega
 * encima de lo que la aplicacion ya tiene.
 */
final class HostApplicationIsolationTest extends TestCase
{
    public static int $appRouteLoads = 0;

    protected function defineEnvironment($app): void
    {
        self::$appRouteLoads = 0;

        AppRouteServiceProvider::loadRoutesUsing(function () {
            self::$appRouteLoads++;
        });

        $app->booting(function ($app) {
            $app->register(AppRouteServiceProvider::class, force: true);
            $app->register(AppEventServiceProvider::class);
        });
    }

    protected function tearDown(): void
    {
        AppRouteServiceProvider::loadRoutesUsing(null);

        parent::tearDown();
    }

    #[Test]
    public function la_aplicacion_carga_sus_rutas_una_sola_vez(): void
    {
        $this->assertSame(1, self::$appRouteLoads);
    }

    #[Test]
    public function las_rutas_del_paquete_conservan_nombre_uri_metodo_y_middleware(): void
    {
        $controller = 'Innoboxrr\LaravelUploads\Http\Controllers\UploadController';

        $expected = [
            'lu.upload.file' => ['POST', 'lu/upload/file', 'uploadFile'],
            'lu.upload.display' => ['GET', 'lu/upload/{upload_uuid}/display/{filename?}', 'display'],
            'lu.upload.delete' => ['DELETE', 'lu/upload/delete', 'delete'],
            'lu.upload.restore' => ['POST', 'lu/upload/restore', 'restore'],
            'lu.upload.force.delete' => ['DELETE', 'lu/upload/force-delete', 'forceDelete'],
        ];

        foreach ($expected as $name => [$method, $uri, $action]) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Falta la ruta {$name}.");
            $this->assertSame($uri, $route->uri(), $name);
            $this->assertContains($method, $route->methods(), $name);
            $this->assertSame(['api'], $route->middleware(), $name);
            $this->assertSame("{$controller}@{$action}", $route->getAction('uses'), $name);
        }
    }
}
