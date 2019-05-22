<?php
/**
 * User: Annie
 * Date: 2019/02/15
 * Time: 上午 10:03
 */

namespace App\Models\AVR;



use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends AVRBaseModel
{
    use SoftDeletes;

    protected $table = 'fimages';

    public function __construct(){
    
    }
    
    
    
}
