<?php
namespace App\Traits;

use App\Scope\BackendSoftDeletes as BackendSoftDeletesScope;

trait BackendSoftDeletes
{
    public static function bootBackendSoftDeletes()
    {
        static::addGlobalScope(new BackendSoftDeletesScope);
    }
}
