<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/26
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\CityPass;

use Ksd\Mediation\Helper\EnvHelper;


class SalesRule extends Client
{
    use EnvHelper;


    /**
     * 使用優惠券
     * @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {

        $response = $this->putParameters($parameters)->request('POST', 'DiscountCodeAddMoreCarts/add');
        $result = json_decode($response->getBody(), true);
        return $result;

    }

    /**
     * 取消優惠券
     * @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {

        $response = $this->putParameters($parameters)->request('POST', 'DiscountCodeAddMoreCarts/remove');

        $result = json_decode($response->getBody(), true);
        return $result;
//        return ($result['statusCode'] === 203) ? true : false;
    }


}
