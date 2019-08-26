<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoteGiftImg extends Model
{
    protected $primaryKey = 'promote_gift_img_id';
    protected $table = 'promote_gift_imgs';
    protected $guarded = ['id'];
    protected $connection = 'backend';
}
