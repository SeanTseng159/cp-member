<?php
namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Supplier extends BaseModel
{
	protected $primaryKey = 'supplier_id';

	public $timestamps = false;
}
