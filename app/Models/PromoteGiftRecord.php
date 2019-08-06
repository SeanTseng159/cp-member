<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoteGiftRecord extends Model
{
    protected $table = 'promote_gift_records';
    protected $guarded = ['id'];
    protected $connection = 'backend';

    public function promoteGift()
    {
        return $this->belongsTo(PromoteGift::class, 'promote_gift_id');
    }
}
