# Laravel Uploads

**One upload model, one API, soft-deletable.**

File uploads scattered across a dozen tables is how you end up with orphaned objects in storage and no way to audit what a user actually sent you. This package gives uploads their own first-class model, with a small HTTP API around it.

## Install

```bash
composer require innoboxrr/laravel-uploads
php artisan vendor:publish --tag=config   # optional: config/laravel-uploads.php
php artisan migrate
```

The host application is expected to provide:

- **Laravel Sanctum.** Every endpoint except `display` uses the `auth:sanctum` middleware, so a SPA with a Sanctum cookie session works as-is.
- **A `users` table.** `uploads.user_id` is a foreign key to it.
- **Optionally, `isAdmin()` on the user model.** When present and `true`, that user may manage any upload (see [Authorization](#authorization)).

## Configuration

| Key | Default | |
|---|---|---|
| `disk` | `env('LARAVEL_UPLOADS_DISK', 's3')` | Where files are stored. A new app without S3 sets `LARAVEL_UPLOADS_DISK=public` (or `local`). |
| `max_size` | `env('LARAVEL_UPLOADS_MAX_SIZE', 10240)` | Maximum size in kilobytes. PHP's own `upload_max_filesize` / `post_max_size` also apply. |
| `allowed_mimes` | images, pdf, office, csv, txt | Extensions for Laravel's `mimes` rule, detected from the file content. SVG is not allowed by default because it is served inline. An empty list disables the type check. |
| `compress_images` | `true` | JPEG, PNG and GIF are scaled down to `compress_images_max_width` with `compress_images_quality`. |
| `production_dir` / `development_dir` | `files` / `test` | Directory inside the disk, chosen by `APP_ENV`. |

## Endpoints

All routes are registered under the `api` middleware group with the `lu.upload.` name prefix.

| Method | URI | Name | |
|---|---|---|---|
| `POST` | `/lu/upload/file` | `lu.upload.file` | Multipart: `file` (required), `visibility` (`public` or `private`), `uploadable_type`, `uploadable_id`. Returns `201`, or `422` when validation fails. |
| `GET` | `/lu/upload/{upload_uuid}/display/{filename?}` | `lu.upload.display` | Streams the file. Public, no authentication; the file name is optional and only decorative. Works with `s3`, `public` and `local` disks. |
| `DELETE` | `/lu/upload/delete` | `lu.upload.delete` | `upload_id`. Soft delete. |
| `POST` | `/lu/upload/restore` | `lu.upload.restore` | `upload_id`. |
| `DELETE` | `/lu/upload/force-delete` | `lu.upload.force.delete` | `upload_id`. Removes the record **and the stored file**. |

A successful upload returns the model wrapped as a JSON resource:

```json
{
  "data": {
    "id": 1,
    "uuid": "9b1d...",
    "filename": "avatar.png",
    "mime_type": "image/png",
    "extension": "png",
    "size": 48213,
    "path": "files/Hk3...png",
    "disk": "public",
    "visibility": "public",
    "user_id": 7,
    "url": "https://app.test/lu/upload/9b1d.../display/avatar.png",
    "uri": "/lu/upload/9b1d.../display/avatar.png"
  }
}
```

If the application calls `JsonResource::withoutWrapping()`, the same object comes without the `data` key.

## Authorization

`UploadPolicy` type-hints `Illuminate\Contracts\Auth\Authenticatable`, never a concrete user class.

| Ability | Who |
|---|---|
| `upload` | Any authenticated user. |
| `delete`, `restore` | The user who uploaded the file, or an admin. |
| `forceDelete` | Admins only. |

"Admin" means the user model has an `isAdmin()` method that returns `true`. Without that method nobody is an admin, and the answer is a plain `403`.

## Attaching uploads to your models

```php
use Innoboxrr\LaravelUploads\Support\Traits\HasUploads; // morphMany
use Innoboxrr\LaravelUploads\Support\Traits\HasUpload;  // morphOne

class Invoice extends Model
{
    use HasUploads;
}

$invoice->uploads; // uploads sent with uploadable_type/uploadable_id of this invoice
```

**Soft delete first.** A deleted upload is recoverable until an admin deliberately destroys it.

**UUID lookups.** Files are addressed by UUID rather than sequential ID, so a display URL does not leak how many files exist or let anyone enumerate them. Note that `visibility` is stored and applied to the object in storage, but the `display` route does not check it: anyone holding the UUID can fetch the file.

## Related

- [`s3-resumable-uploads`](https://github.com/innoboxrr/s3-resumable-uploads) — for large files that need to survive interrupted connections.
- [`aws-file-manager`](https://github.com/innoboxrr/aws-file-manager) — browsing and managing S3 objects directly.

---

Part of [Innobox R&R](https://github.com/innoboxrr) — 52 open-source packages extracted from production work. **[innobox.systems](https://innobox.systems)**
