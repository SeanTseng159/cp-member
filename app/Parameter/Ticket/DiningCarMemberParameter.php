<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Parameter\Ticket;

use App\Parameter\BaseParameter;

class DiningCarMemberParameter extends BaseParameter
{
	public function __construct($request)
    {
    	parent::__construct($request);
    }

    public function list()
    {
        $params['page'] = $this->page;
        $params['limit'] = $this->limit;

		return $params;
    }
}
