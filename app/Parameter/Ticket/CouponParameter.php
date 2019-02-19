<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 10:50
 */

namespace App\Parameter\Ticket;

use App\Parameter\BaseParameter;

class CouponParameter extends BaseParameter
{

	public function __construct($request)
    {
    	parent::__construct($request);
    	
    	$this->modelType = $request->modelType;
        $this->modelSpecId = $request->modelSpecId;
        
        return $this;
    }
    

    
}
