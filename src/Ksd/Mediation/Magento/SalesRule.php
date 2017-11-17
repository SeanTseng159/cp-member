<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 3:51
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Result\SalesRule\CouponResult;
use Ksd\Mediation\Result\SalesRule\SalesRuleResult;

class SalesRule extends Client
{
    public function find($id)
    {
        $url = sprintf('V1/salesRules/%s', $id);
        $this->clear();
        $response = $this->request('GET', $url);
        $result = json_decode($response->getBody(), true);

        $salesRule = new SalesRuleResult();
        $salesRule->magento($result);

        return $salesRule;
    }

    public function couponFindByCode($code)
    {
        $url = 'V1/coupons/search';
        $this->clear();
        $this->putQueries([
            'searchCriteria[filterGroups][0][filters][0][field]' => 'code',
            'searchCriteria[filterGroups][0][filters][0][value]' => $code,
            'searchCriteria[pageSize]' => 1
        ]);
        $response = $this->request('GET', $url);
        $result = json_decode($response->getBody(), true);
        $item = empty($result['items']) ? [] : $result['items'][0];

        $coupon = new CouponResult();
        $coupon->magento($item);
        return $coupon;
    }

    public function couponDetail($code)
    {
        $coupon = $this->couponFindByCode($code);
        $salesRule = $this->find($coupon->ruleId);
        $salesRule->setCoupon($coupon);

        return $salesRule;
    }

    public function useCoupon()
    {
        $url = 'V1/carts/mine/coupons';
        $response = $this->request('GET', $url);
        $result = $response->getBody();
        return $result;
    }

    /**
     * 使用折扣優惠
     * @param $code
     * @return bool
     */
    public function addCoupon($code)
    {
        $url = sprintf('V1/carts/mine/coupons/%s', $code);
        $response = $this->request('PUT', $url);
        $result = $response->getBody();

        return $result == 'true';
    }

    /**
     * 取消折扣優惠
     * @return bool
     */
    public function deleteCoupon()
    {
        $url = 'V1/carts/mine/coupons';
        $response = $this->request('DELETE', $url);
        $result = $response->getBody();
        return $result === 'true';
    }
}