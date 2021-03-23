<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:28
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\SalesRuleRepository;

class SalesRuleService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new SalesRuleRepository($memberTokenService);
    }
    /**
     * 使用折扣優惠
     * @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {
        return $this->repository->addCoupon($parameters);

    }

    /**
     * 取消折扣優惠
     * @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {
        return $this->repository->deleteCoupon($parameters);
    }


    /**
     * 使用"店家新增之優惠券"的折扣優惠
     * @param $parameters
     * @return array
     */
    public function addCouponOnline($parameters,$memberID)
    {
        //新增流程 1.取Coupon的基本資料 -> 2.判斷此購物車是否能使用此Coupon -> 3.計算各種金額(原價、折價後金額、折抵多少...)

        //DEBUG
        

        //透過Coupon代碼取Coupon基本資料
        $coupon_online_code = $parameters->code;
        $coupon_data =  $this->repository->getCouponOnlineDataByCode($coupon_online_code);

        //判斷此張優惠是否還能使用(逾期、次數不足)
        $canUseCoupon = $this->repository->checkCouponOnlineCanUsed($coupon_data,$memberID);
        //$canUseCoupon =>  {canUsed:true, error_message:} or {canUsed:false, error_message:"此優惠券已達每名會員可使用上限次數/.../..."}


        //此部分功能要將"優惠券資料"與"優惠券能否使用"merge成同一個array
        $coupon_data = array($coupon_data);
        //return $coupon_data;
        $coupon_data[0]['canUsed'] = $canUseCoupon['canUsed'];
        $coupon_data[0]['error_message'] = $canUseCoupon['error_message'];
        return $coupon_data;

    }


}