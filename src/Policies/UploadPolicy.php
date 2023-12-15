<?php

namespace Innoboxrr\LaravelUploads\Policies;

use App\Models\User;
use Innoboxrr\LaravelUploads\Models\Upload;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
{
    use HandlesAuthorization;

    public function upload(User $user)
    {
        return true;
    }

    public function display(User $user, Upload $upload)
    {
        return true;
    }

    public function delete(User $user, Upload $upload)
    {
        return true;
    }

    public function restore(User $user, Upload $upload)
    {
        return true;
    }

    public function forceDelete(User $user, Upload $upload)
    {
        return true;
    }

}
