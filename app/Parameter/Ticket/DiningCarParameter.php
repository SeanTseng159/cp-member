<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Parameter\Ticket;

use Carbon\Carbon;
use App\Parameter\BaseParameter;

class DiningCarParameter extends BaseParameter
{
	public function __construct($request)
    {
    	parent::__construct($request);
    }

    public function list()
    {
    	// 預設高雄火車站
    	$this->longitude = $this->request->input('longitude', '120.3045522');
		$this->latitude = $this->request->input('latitude', '22.6402112');

		return $this;
    }
}
