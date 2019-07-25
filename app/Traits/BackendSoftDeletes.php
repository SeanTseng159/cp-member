<?php
namespace App\Traits;

use App\Scopes\BackendSoftDeletes as BackendSoftDeletesScope;

trait BackendSoftDeletes
{
    public static function bootBackendSoftDeletes()
    {
        static::addGlobalScope(new BackendSoftDeletesScope);
    }
}
