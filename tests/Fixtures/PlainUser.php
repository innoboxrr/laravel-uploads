<?php

namespace Innoboxrr\LaravelUploads\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Un usuario de host sin isAdmin(): la policy tiene que negar limpio, no
 * reventar con un metodo inexistente.
 */
class PlainUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];
}
