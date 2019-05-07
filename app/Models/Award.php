<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $guarded = ['id'];
    protected $primaryKey = 'award_id';
    protected $table = 'awards';
    protected $connection = 'backend';



    public function image()
    {
        return $this->hasOne(AwardImage::class, 'award_id');
    }
}
