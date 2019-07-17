<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PromoteGift extends Model
{
    protected $table = 'promote_gifts';
    protected $guarded = ['id'];
    protected $connection = 'backend';
}
