<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
class PromoteGiftRecord extends Model
{
    protected $table = 'promote_gift_records';
    protected $guarded = ['id'];
    protected $connection = 'backend';
}
