<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $guarded = ['id'];
    protected $primaryKey = 'award_id';
    protected $table = 'awards';
    protected $connection = 'backend';
    public $timestamps = false;


    public function image()
    {
        return $this->hasOne(AwardImage::class, 'award_id', 'award_id');
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'supplier_id', 'supplier_id');
    }
}
