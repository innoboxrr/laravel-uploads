<?php

namespace Innoboxrr\LaravelUploads\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Un usuario de host que expone isAdmin(), como el que genera laravel-setup.
 */
class User extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
