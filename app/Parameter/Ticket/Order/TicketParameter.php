<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Parameter\Ticket\Order;

use App\Parameter\BaseParameter;
use App\Config\Ticket\TicketConfig;

class TicketParameter extends BaseParameter
{
	public $status;

	public function __construct($request)
    {
    	parent::__construct($request);
    }

    public function all()
    {
    	$this->limit = $this->limit ?: 300;
        $this->page = $this->page ?: 1;
    	$this->orderStatus = $this->request->status;
    	$this->status = isset(TicketConfig::DB_STATUS[$this->orderStatus]) ? TicketConfig::DB_STATUS[$this->orderStatus] : null;

    	return $this;
    }
}
