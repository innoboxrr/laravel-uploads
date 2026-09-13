<?php

namespace Innoboxrr\LaravelUploads\Tests\Feature\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Support\Services\UploadService;
use Innoboxrr\LaravelUploads\Tests\FeatureTestCase;
use Innoboxrr\LaravelUploads\Tests\Fixtures\PlainUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * El flujo del avatar del admin: subir con la sesion de Sanctum, mostrar por
 * la URL que devuelve la respuesta y administrar el registro.
 */
final class UploadEndpointsTest extends FeatureTestCase
{
    #[Test]
    public function un_invitado_recibe_401(): void
    {
        $this->postJson('/lu/upload/file', ['file' => UploadedFile::fake()->image('avatar.png')])
            ->assertUnauthorized();

        $this->deleteJson('/lu/upload/delete', ['upload_id' => 1])->assertUnauthorized();
        $this->postJson('/lu/upload/restore', ['upload_id' => 1])->assertUnauthorized();
        $this->deleteJson('/lu/upload/force-delete', ['upload_id' => 1])->assertUnauthorized();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function disks(): array
    {
        return ['public' => ['public'], 'local' => ['local']];
    }

    #[Test]
    #[DataProvider('disks')]
    public function sube_una_imagen_y_la_muestra_con_y_sin_nombre(string $disk): void
    {
        config(['laravel-uploads.disk' => $disk]);

        $user = $this->makeUser();
        $this->signIn($user);

        $response = $this->post('/lu/upload/file', [
            'file' => UploadedFile::fake()->image('avatar.png', 300, 300),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'uuid', 'filename', 'mime_type', 'size', 'path', 'disk', 'visibility', 'user_id', 'url', 'uri']])
            ->assertJsonPath('data.filename', 'avatar.png')
            ->assertJsonPath('data.disk', $disk)
            ->assertJsonPath('data.visibility', 'public')
            ->assertJsonPath('data.user_id', $user->id);

        $upload = Upload::where('uuid', $response->json('data.uuid'))->firstOrFail();

        Storage::disk($disk)->assertExists($upload->path);
        $this->assertSame("/lu/upload/{$upload->uuid}/display/avatar.png", $response->json('data.uri'));

        $stored = Storage::disk($disk)->get($upload->path);

        $withName = $this->get($response->json('data.uri'));
        $withName->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertSame($stored, $withName->streamedContent());

        $withoutName = $this->get("/lu/upload/{$upload->uuid}/display");
        $withoutName->assertOk();
        $this->assertSame($stored, $withoutName->streamedContent());
    }

    /**
     * Laravel 13 publica cache.serializable_classes => false. Con un store que
     * serializa, un modelo cacheado vuelve como __PHP_Incomplete_Class y la
     * segunda visita a la misma imagen reventaba.
     */
    #[Test]
    public function la_segunda_visita_funciona_con_la_cache_de_laravel_13(): void
    {
        config([
            'cache.default' => 'array',
            'cache.stores.array.serialize' => true,
            'cache.serializable_classes' => false,
        ]);
        Cache::purge('array');

        $user = $this->makeUser();
        $upload = $this->storedUpload($user);

        $this->get("/lu/upload/{$upload->uuid}/display/{$upload->filename}")->assertOk();
        $this->get("/lu/upload/{$upload->uuid}/display/{$upload->filename}")->assertOk();
    }

    #[Test]
    public function la_visibilidad_privada_se_guarda(): void
    {
        $this->signIn($this->makeUser());

        $this->post('/lu/upload/file', [
            'file' => UploadedFile::fake()->create('contrato.pdf', 20, 'application/pdf'),
            'visibility' => 'private',
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.visibility', 'private');
    }

    #[Test]
    public function sin_archivo_responde_422(): void
    {
        $this->signIn($this->makeUser());

        $this->postJson('/lu/upload/file', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Upload::count());
    }

    #[Test]
    public function un_tipo_no_permitido_responde_422(): void
    {
        $this->signIn($this->makeUser());

        $this->post('/lu/upload/file', [
            'file' => UploadedFile::fake()->create('paquete.zip', 10, 'application/zip'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Upload::count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    #[Test]
    public function un_archivo_demasiado_grande_responde_422(): void
    {
        config(['laravel-uploads.max_size' => 100]);

        $this->signIn($this->makeUser());

        $this->post('/lu/upload/file', [
            'file' => UploadedFile::fake()->create('grande.pdf', 101, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Upload::count());
    }

    #[Test]
    public function una_visibilidad_desconocida_responde_422(): void
    {
        $this->signIn($this->makeUser());

        $this->post('/lu/upload/file', [
            'file' => UploadedFile::fake()->image('avatar.png'),
            'visibility' => 'everyone',
        ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('visibility');
    }

    #[Test]
    public function el_duenio_borra_y_restaura_su_archivo(): void
    {
        $owner = $this->makeUser();
        $upload = $this->storedUpload($owner);

        $this->signIn($owner);

        $this->deleteJson('/lu/upload/delete', ['upload_id' => $upload->id])->assertOk();
        $this->assertSoftDeleted($upload);
        $this->get("/lu/upload/{$upload->uuid}/display")->assertNotFound();

        $this->postJson('/lu/upload/restore', ['upload_id' => $upload->id])->assertOk();
        $this->assertNotSoftDeleted($upload);
        $this->get("/lu/upload/{$upload->uuid}/display")->assertOk();
    }

    #[Test]
    public function borrar_el_archivo_de_otro_responde_403(): void
    {
        $upload = $this->storedUpload($this->makeUser());

        $this->signIn($this->makeUser());

        $this->deleteJson('/lu/upload/delete', ['upload_id' => $upload->id])->assertForbidden();
        $this->postJson('/lu/upload/restore', ['upload_id' => $upload->id])->assertForbidden();
        $this->assertNotSoftDeleted($upload);
    }

    #[Test]
    public function un_usuario_sin_is_admin_recibe_403_y_no_un_error(): void
    {
        $upload = $this->storedUpload($this->makeUser());
        $stranger = $this->makeUser();

        $this->signIn(PlainUser::findOrFail($stranger->id));

        $this->deleteJson('/lu/upload/delete', ['upload_id' => $upload->id])->assertForbidden();
        $this->deleteJson('/lu/upload/force-delete', ['upload_id' => $upload->id])->assertForbidden();
    }

    #[Test]
    public function un_admin_borra_cualquier_archivo(): void
    {
        $upload = $this->storedUpload($this->makeUser());

        $this->signIn($this->makeUser(['is_admin' => true]));

        $this->deleteJson('/lu/upload/delete', ['upload_id' => $upload->id])->assertOk();
        $this->assertSoftDeleted($upload);

        $this->postJson('/lu/upload/restore', ['upload_id' => $upload->id])->assertOk();
        $this->assertNotSoftDeleted($upload);
    }

    #[Test]
    public function el_borrado_definitivo_lo_decide_la_policy_y_quita_el_archivo(): void
    {
        $owner = $this->makeUser();
        $upload = $this->storedUpload($owner);

        $this->signIn($owner);
        $this->deleteJson('/lu/upload/force-delete', ['upload_id' => $upload->id])->assertForbidden();
        Storage::disk('public')->assertExists($upload->path);

        $this->signIn($this->makeUser(['is_admin' => true]));
        $this->deleteJson('/lu/upload/force-delete', ['upload_id' => $upload->id])->assertOk();

        $this->assertModelMissing($upload);
        Storage::disk('public')->assertMissing($upload->path);
    }

    #[Test]
    public function get_file_info_usa_la_api_de_flysystem_3(): void
    {
        Storage::disk('public')->put('test/nota.txt', 'hola');

        $info = (new UploadService())->getFileInfo('test/nota.txt');

        $this->assertSame('test/nota.txt', $info['path']);
        $this->assertSame(4, $info['size']);
        $this->assertSame('text/plain', $info['mime_type']);
        $this->assertIsInt($info['last_modified']);
    }

    private function storedUpload($user): Upload
    {
        Storage::disk('public')->put('test/archivo.txt', 'contenido');

        return Upload::factory()->create([
            'filename' => 'archivo.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 9,
            'path' => 'test/archivo.txt',
            'disk' => 'public',
            'user_id' => $user->id,
        ]);
    }
}
