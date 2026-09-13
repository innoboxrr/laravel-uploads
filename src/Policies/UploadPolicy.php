<?php

namespace Innoboxrr\LaravelUploads\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Innoboxrr\LaravelUploads\Models\Upload;

/*
 * Sin App\Models\User en las firmas: el paquete no puede suponer la clase de
 * usuario del host, y una firma que no coincide es un TypeError, no un 403.
 */
class UploadPolicy
{
    use HandlesAuthorization;

    public function upload(Authenticatable $user)
    {
        return true;
    }

    public function display(?Authenticatable $user, Upload $upload)
    {
        return true;
    }

    public function delete(Authenticatable $user, Upload $upload)
    {
        return $this->owns($user, $upload) || $this->isAdmin($user);
    }

    public function restore(Authenticatable $user, Upload $upload)
    {
        return $this->owns($user, $upload) || $this->isAdmin($user);
    }

    // Borra el archivo sin vuelta atras: solo un administrador.
    public function forceDelete(Authenticatable $user, Upload $upload)
    {
        return $this->isAdmin($user);
    }

    protected function owns(Authenticatable $user, Upload $upload): bool
    {
        return $upload->user_id !== null
            && (string) $upload->user_id === (string) $user->getAuthIdentifier();
    }

    protected function isAdmin(Authenticatable $user): bool
    {
        return method_exists($user, 'isAdmin') && (bool) $user->isAdmin();
    }

}
