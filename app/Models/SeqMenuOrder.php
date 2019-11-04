<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class SeqMenuOrder extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'seq_menu_orders';
    protected $connection = 'backend';
    protected $fillable = ['order_number', 'updated_at'];
}
