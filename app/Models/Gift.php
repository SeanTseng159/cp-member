<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Helpers\ClientType;
use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;

class Gift extends BaseModel
{
    protected $table = 'gifts';
    protected $clientType = ['dining_car'];
    protected $connection = 'backend';
    
    public function __construct()
    {
    
    
    }
    
    public function diningCar()
    {
        
        return  $this->hasOne(DiningCar::class, 'id', 'model_spec_id');
        
        
    }
    
    
}
