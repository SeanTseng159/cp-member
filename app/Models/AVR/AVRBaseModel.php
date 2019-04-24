<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/4/22
 * Time: 下午 03:56
 */

namespace App\Models\AVR;

use Illuminate\Database\Eloquent\Model;

class AVRBaseModel extends Model
{
    protected $connection = 'avr';

}