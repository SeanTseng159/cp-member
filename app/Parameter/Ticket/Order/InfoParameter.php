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

	public function __construct($request)
    {
    	parent::__construct($request);

    	$today = Carbon::today();
		$endDate = $today->addDays(1)->format('Y-m-d');
		$startDate = $today->subMonths(3)->format('Y-m-d'); // 往後推三個月

		$this->startDate = $this->request->input('startDate', $startDate);
		$this->endDate = $this->request->input('endDate', $endDate);
    }
}
