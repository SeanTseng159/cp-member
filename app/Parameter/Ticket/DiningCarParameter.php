<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Parameter\Ticket;

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
    	$params['longitude'] = $this->request->input('longitude', '120.3045522');
		$params['latitude'] = $this->request->input('latitude', '22.6402112');
        $params['keyword'] = $this->request->input('keyword');
        $params['county'] = $this->request->input('county');
        $params['category'] = $this->request->input('category');
        $params['openStatus'] = $this->request->input('openStatus');
        $params['page'] = $this->page;
        $params['limit'] = $this->limit;

		return $params;
    }
}
