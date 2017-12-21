<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 04:45
 */

namespace Ksd\Mediation\Parameter\MyTicket;

use Ksd\Mediation\Parameter\BaseParameter;
use App\Services\MemberService;

class QueryParameter extends  BaseParameter
{
    private $memberService;

    public function __construct()
    {


    }


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

        $this->country = $request->input('country');
        $this->countryCode = $request->input('countryCode');
        $this->memberPhone = $request->input('toMemberPhone');


    }
}