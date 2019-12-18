<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class TagEvent extends BaseModel
{
    protected $table = 'tags_event';
    protected $primaryKey = 'id';
}
