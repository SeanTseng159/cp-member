<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 04:45
 */

namespace Ksd\Mediation\Parameter\MyTicket;

use Ksd\Mediation\Parameter\BaseParameter;


class QueryParameter extends  BaseParameter
{
    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($no, $input = null)
    {
        parent::codeigniterRequest($input);
        $this->no = $no;

    }


    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->serialNumber= $request->input('serialNumber');
        $this->toMemberId = $request->input('toMemberId');

    }
}
{

}