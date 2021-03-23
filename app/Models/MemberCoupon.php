<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;


/**
 * @property  int member_id
 * @property  int coupon_id
 * @property int  count
 * @property boolean  is_collected
 */
class MemberCoupon extends BaseModel
{                            
    protected $table = 'member_coupon';
    
    public function __construct(){
    
    }

}
