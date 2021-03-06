<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Parameter\Ticket\Order;

use Carbon\Carbon;
use App\Parameter\BaseParameter;

class InfoParameter extends BaseParameter
{
	public $startDate;
	public $endDate;
	public $parameters;

	public function __construct($request)
    {
    	parent::__construct($request);

    	$this->parameters['memberId'] = $request->memberId;
    }

    public function info()
    {
    	$today = Carbon::today();
		$this->endDate = $today->addDays(1)->format('Y-m-d 23:59:59');
		$this->startDate = $today->subMonths(6)->format('Y-m-d 00:00:00'); // 往後推六個月

    	$this->parameters['startDate'] = $this->startDate;
		$this->parameters['endDate'] = $this->endDate;

		$this->parameters['status'] = '99';
		$this->parameters['orderNo'] = null;

		return $this->parameters;
    }

    public function search()
    {
    	$this->parameters['startDate'] = $this->request->input('startDate');
		$this->parameters['endDate'] = $this->request->input('endDate');

		$this->parameters['status'] = $this->request->input('status', '99');
		$this->parameters['orderNo'] = $this->request->input('orderNo');


		if ($this->parameters['startDate']) {
			$this->parameters['startDate'] = Carbon::parse($this->parameters['startDate'])->format('Y-m-d 00:00:00');
		}
		if ($this->parameters['endDate']) {
			$this->parameters['endDate'] = Carbon::parse($this->parameters['endDate'])->format('Y-m-d 23:59:59');
		}

		return $this->parameters;
    }
}
