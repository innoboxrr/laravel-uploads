# Changelog

Todas las modificaciones notables a este proyecto serán documentadas en este archivo.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
y este proyecto sigue [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.1] - 2026-09-13

Lo que el paquete le hacía a la aplicación de Laravel 11+ que lo instala al
arrancar.

### Corregido

- La aplicación cargaba sus `routes/web.php` y `api.php` una vez más por este
  paquete: `RouteServiceProvider` heredaba del de Foundation, que vuelve a
  ejecutar el cargador de `withRouting()`. Ahora hereda de
  `Illuminate\Support\ServiceProvider` y registra las rutas en `boot()`, salvo
  con las rutas cacheadas.
- Cada usuario nuevo recibía repetido el correo de verificación:
  `EventServiceProvider` heredaba del de Foundation, que agrega otro
  `SendEmailVerificationNotification` para `Registered`.
- `AuthServiceProvider` nunca registraba `UploadPolicy`: armaba mal el nombre
  del modelo, y la policy solo se aplicaba por el adivinador de nombres de
  Laravel. Ahora se registra con `Gate::policy()`.
- Sin la carpeta `src/Http/Events`, el descubrimiento de eventos recorría la
  raíz del disco en cada arranque.

URIs, nombres de ruta, middleware y respuestas no cambian.

## [2.1.0] - 2026-09-13

Lo que hacía falta para que el avatar del admin de una aplicación nueva de
Laravel 13 (SPA con sesión de Sanctum) suba, se muestre y se administre.

### Añadido

- Validación de la subida, que ahora responde 422 en lugar de 500: `file`
  obligatorio, tamaño máximo y tipos permitidos. `visibility` solo acepta
  `public` o `private`.
- Claves de config nuevas: `max_size` (kilobytes, `LARAVEL_UPLOADS_MAX_SIZE`,
  10240 por defecto) y `allowed_mimes` (imágenes, pdf, ofimática, csv y txt;
  SVG no, porque se sirve inline).
- `disk` se lee de `LARAVEL_UPLOADS_DISK`. El valor por defecto sigue siendo
  `s3`, así que las aplicaciones existentes no cambian; una nueva puede usar
  `public` o `local`.
- `innoboxrr/traits ^2.1` e `intervention/image ^3.11` en `require`: el
  paquete los usa en tiempo de ejecución y no los declaraba.
- Suite de pruebas con `Storage::fake`: invitado 401, subida y visualización
  con y sin nombre en discos `public` y `local`, validaciones 422, borrado
  propio, ajeno (403) y de admin. También prueba que arranca y migra con
  `CACHE_STORE=database` sin tabla `cache`; los proveedores no leen la cache
  al arrancar.

### Cambiado

- `UploadPolicy`: borrar y restaurar, solo el dueño del archivo o un admin;
  borrar definitivamente, solo un admin. Antes cualquier usuario autenticado
  podía borrar lo de cualquiera. Admin es el usuario cuyo `isAdmin()` devuelve
  `true`; sin ese método se niega con 403. Las firmas usan
  `Illuminate\Contracts\Auth\Authenticatable` y no `App\Models\User`.
- `force-delete` ya no responde siempre 403: decide la policy, y además borra
  el archivo del disco, como prometía el README.
- El archivo se guarda en el disco con la visibilidad pedida; antes siempre
  quedaba `public` aunque el registro dijera `private`.
- La compresión de imágenes crea el `ImageManager` de Intervention con GD o
  Imagick en lugar de usar el facade `Image`, y respeta `compress_images`. Sin
  ninguna de las dos extensiones se sube el original.
- La cache de `display` guarda solo disco y ruta, con la clave
  `laravel-uploads.display.{uuid}`, y se limpia al borrar o restaurar.

### Corregido

- En Laravel 13 toda subida de imagen respondía 500
  (`ImageManager::usingDriver()`): la clave `image` del contenedor pertenece
  al componente de imagen del framework, que usa la API de Intervention 4.
- `GET lu/upload/{uuid}/display` sin nombre de archivo respondía 500.
- La segunda visita a la misma imagen respondía 500 con
  `cache.serializable_classes => false`, el valor que publica Laravel 13: el
  modelo cacheado volvía como `__PHP_Incomplete_Class`.
- `UploadService::getFileInfo()` llamaba a `getMetadata()`, de Flysystem 1.
- `UploadFactory` no definía ningún atributo.
- README: el tag de publicación es `config`; documenta los endpoints, la
  respuesta, la policy y la configuración reales.

Rutas, nombres de ruta, tabla y claves de config existentes no cambian.
