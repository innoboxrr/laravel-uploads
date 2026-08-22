# Laravel Uploads

**One upload model, one API, soft-deletable.**

File uploads scattered across a dozen tables is how you end up with orphaned S3 objects and no way to audit what a user actually sent you. This package gives uploads their own first-class model, with a REST resource around it.

```php
$upload = $user->uploads()->create([
    'file' => $request->file('document'),
]);

$upload->delete();        // soft delete — recoverable
$upload->restore();       // undo
$upload->forceDelete();   // gone, and the stored object with it
```

## What ships

| | |
|---|---|
| `Upload` model | UUID-indexed, polymorphic owner, soft deletes |
| `UploadController` | Full REST resource — index, display, delete, restore, force delete |
| Form requests | `DisplayRequest`, `DeleteRequest`, `RestoreRequest`, `ForceDeleteRequest` — authorisation per action |
| API routes | Registered under a configurable prefix |
| Migrations | Uploads table plus a dedicated UUID index migration for lookup performance |

**Soft delete first.** A deleted upload is recoverable until someone deliberately destroys it. Users delete things by accident; the storage bill is cheaper than the support ticket.

**UUID lookups.** Files are addressed by UUID rather than sequential ID, so an upload URL does not leak how many files exist or let anyone enumerate them.

## Install

```bash
composer require innoboxrr/laravel-uploads
php artisan vendor:publish --tag=laravel-uploads-config
php artisan migrate
```

## Related

- [`s3-resumable-uploads`](https://github.com/innoboxrr/s3-resumable-uploads) — for large files that need to survive interrupted connections.
- [`aws-file-manager`](https://github.com/innoboxrr/aws-file-manager) — browsing and managing S3 objects directly.

---

Part of [Innobox R&R](https://github.com/innoboxrr) — 52 open-source packages extracted from production work. **[innobox.systems](https://innobox.systems)**
