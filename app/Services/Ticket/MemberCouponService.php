<?php
/**
 * User: annie
 * Date: 2019/2/15
 * Time: 下午 02:40
 */


namespace App\Services\Ticket;

use App\Repositories\Ticket\MemberCouponRepository;
use App\Services\BaseService;

class MemberCouponService extends BaseService
{
    protected $repository;
    
    public function __construct(MemberCouponRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * 取得使用者之優惠卷列表
     *
     * @param      $memberID
     * @param null $couponId
     *
     * @return mixed
     */
    public function list($memberID,$couponId = null)
    {
        return $this->repository->list($memberID,$couponId);
    }
    
    /**
     * 取得使用者之優惠卷的資訊列表
     *
     * @param      $memberID
     *
     * @param      $status
     *                      current 未使用 1
     *                      used    已使用 2
     *                      expired 已失效 3
     *
     * @return mixed
     */
    public function favoriteCouponList($memberID,$status)
    {
        return $this->repository->favoriteCouponList($memberID,$status);
    }
    
    /**
     * 取得使用者之優惠卷明細
     *
     *
     * @param $memberID
     * @param $couponId
     *
     * @return mixed
     */
    public function find($memberID,$couponId)
    {
        return $this->repository->list($memberID,$couponId)->first();
    }
    
    
    /** 將優惠卷加入使用者的收藏
     *
     * @param $memberID
     * @param $couponId
     *
     * @return \App\Models\MemberCoupon
     */
    public function add($memberID,$couponId)
    {
        return $this->repository->add($memberID,$couponId);
    }
    
    
    /** 更新優惠卷是否在使用者的收藏列表內
     *
     * @param $memberID
     * @param $couponId
     *
     * @param $isFavorite
     *
     * @return \App\Models\MemberCoupon
     */
    public function update($memberID,$couponId,$isFavorite)
    {
        return $this->repository->update($memberID,$couponId,$isFavorite);
    }
    
    
    /**
     * 使用優惠卷
     *
     * @param $memberId
     * @param $couponID
     *
     * @return mixed
     */
    public function use($memberId,$couponID)
    {
        return $this->repository->use($memberId, $couponID);
        
    }

    public function availableCoupons($memberId)
    {
        return $this->repository->availableCoupons($memberId);
    }
}
