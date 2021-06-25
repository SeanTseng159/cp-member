<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;


use App\Repositories\Ticket\CouponRepository;
use App\Services\BaseService;
use App\Services\ImageService;

class CouponService extends BaseService
{
    protected $repository;
    
    public function __construct(CouponRepository $repository, ImageService $imgService)
    {
        $this->repository = $repository;
        $this->imgService = $imgService;
    }

    /**
     * 取得該店家(或餐車)之優惠卷列表
     * @param $modelSpecID
     * @param $modelType
     * @return mixed
     */
    public function list($modelSpecID,$modelType)
    {
        return $this->repository->list($modelSpecID,$modelType);
    }

    //把從repository拿到的優惠券資料，去ImageService內取得圖片路徑，再把路徑補進優惠券object裡面
    public function getCouponImgUrl($ImgObj)
    {
        $data = $ImgObj;
        //將優惠券資料obj轉成array以方便新增屬性imgUrl，並將優惠券的圖片路徑存在imgUrl屬性內
        $data = array($data);
        $get_img_array = $data[0];
        foreach($get_img_array as $key=>$value){
            $img_obj = $this->imgService->path('coupon',$value->id,'1');
            $value->imgUrl = $img_obj[0]->folder.$img_obj[0]->filename.'.'.$img_obj[0]->ext;
        }
        return (object)$get_img_array;
    }

    /**
     * 取得會員領取店家優惠 可使用
     * @param $memberID
     * @return mixed
     */
    public function memberCurrentCouponlist($memberID) 
    {
        $data = $this->repository->memberCurrentCouponlist($memberID);//取出優惠券資料
        return $this->getCouponImgUrl($data);
    }

    /**
     * 取得會員領取店家優惠 已使用
     * @param $memberID
     * @return mixed
     */
    public function memberUsedCouponlist($memberID) 
    {
        $data = $this->repository->memberUsedCouponlist($memberID);
        return $this->getCouponImgUrl($data);
    }

    /**
     * 取得會員領取店家優惠 已失效
     * @param $memberID
     * @return mixed
     */
    public function memberDisabledCouponlist($memberID) 
    {
        $data = $this->repository->memberDisabledCouponlist($memberID);
        return $this->getCouponImgUrl($data);
    }

    /**
     * 會員領取店家優惠券
     * @param $data
     * @return mixed
     */
    public function createAndCheck($data){
        return $this->repository->createAndCheck($data);
    }
    
    /**
     * 取詳細coupon資料
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
    }

    /**
     * 依據優惠卷編號，查詢coupon資料
     * @param $code
     * @return mixed
     */
    public function getEnableCouponByCode($code) 
    {
        return $this->repository->getEnableCouponByCode($code);
    }

    //取得優惠卷倒數過期前7天前資料
    public function findCouponEndTime()
    {
        return $this->repository->findCouponEndTime();
    } 

    public function checkEnableAndExistByCode($code)
    {
        return $this->repository->checkEnableAndExistByCode($code);
    }
    
    /**
     * 根據 店車id 取得目前此時所有可以使用的線上優惠券
     *
     * @param  mixed $dining_car_id
     * @return void
     */
    public function listCanUsedByDiningCarId($dining_car_id)
    {
        return $this->repository->listCanUsedByDiningCarId($dining_car_id);
    }

}
