<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Parameter\Ticket;

use App\Parameter\BaseParameter;
use App\Traits\MapHelper;

class DiningCarParameter extends BaseParameter
{
    use MapHelper;

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
        $params['memberId'] = $this->memberId;

		return $params;
    }

    public function map()
    {
        $params['longitude'] = $this->request->input('longitude', '120.3045522');
        $params['latitude'] = $this->request->input('latitude', '22.6402112');

        // 範圍
        $minLatitude = (float) $this->request->input('minLatitude', -90);
        $maxLatitude = (float) $this->request->input('maxLatitude', 90);
        $minLongitude = (float) $this->request->input('minLongitude', -180);
        $maxLongitude = (float) $this->request->input('maxLongitude', 180);
        $params['range'] = $this->calcMapRange($minLatitude, $maxLatitude, $minLongitude, $maxLongitude);

        $params['keyword'] = $this->request->input('keyword');
        $params['category'] = $this->request->input('category');
        $params['openStatus'] = $this->request->input('openStatus');

        return $params;
    }

    public function detail()
    {
        // 預設高雄火車站
        $params['longitude'] = $this->request->input('longitude', '120.3045522');
        $params['latitude'] = $this->request->input('latitude', '22.6402112');
        $params['memberId'] = $this->memberId;

        return $params;
    }
}
