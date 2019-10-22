<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = ['id'];
    protected $primaryKey = 'employee_id';
    protected $table = 'employees';
    protected $connection = 'backend';
    public $timestamps = false;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }
}
