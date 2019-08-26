<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoteGift extends Model
{
    protected $table = 'promote_gifts';
    protected $guarded = ['id'];
    protected $connection = 'backend';

    public function image()
    {
        return $this->hasOne(PromoteGiftImg::class);
    }
}
