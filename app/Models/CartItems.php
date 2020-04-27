<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItems extends Model
{
    protected $primaryKey = 'cart_item_id';
    protected $table = 'cart_items';
    protected $connection = 'backend';
}
