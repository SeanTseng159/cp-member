<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardImage extends Model
{

    protected $primaryKey = 'award_img_id';
    protected $table = 'award_imgs';
    protected $connection = 'backend';


}
