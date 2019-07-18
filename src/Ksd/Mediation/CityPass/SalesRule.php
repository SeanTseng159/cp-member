<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/26
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\CityPass;
use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\SalesRule\CouponResult;
use Ksd\Mediation\Result\SalesRule\SalesRuleResult;
use Log;

class SalesRule extends Client
{
    use EnvHelper;


    /**
     * 使用優惠券
     *  @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {

        $response = $this->putParameters($parameters)->request('POST', 'DiscountCode/add');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 201) ? true : false;
    }

    /**
     * 取消優惠券
     *  @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {

        $response = $this->putParameters($parameters)->request('POST', 'DiscountCode/remove');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 203) ? true : false;
    }


}
