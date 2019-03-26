<?php


namespace App\Http\Controllers\Api\V1;


use App\Enum\ClientType;
use App\Helpers\ImageHelper;
use App\Models\Ticket\DiningCar;
use App\Parameter\Ticket\CouponParameter;
use App\Result\Ticket\CouponResult;
use App\Services\Ticket\CouponService;
use App\Services\ImageService;
use App\Services\Ticket\DiningCarService;
use App\Services\Ticket\MemberCouponService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class CouponController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $couponService;
    protected $memberCouponService;
    protected $imageService;
    protected $diningCarService;


    public function __construct(CouponService $service,
                                MemberCouponService $memberCouponService,
                                ImageService $imageService,
                                DiningCarService $diningCarService)
    {
        $this->couponService = $service;
        $this->memberCouponService = $memberCouponService;
        $this->imageService = $imageService;
        $this->diningCarService = $diningCarService;
    }

    /**
     *
     * 根據優惠卷類別與使用優惠卷單位(如餐車商家)之ID取得優惠卷列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {

        try {

            $params = new CouponParameter($request);

            $modelSpecID = $params->modelSpecId;
            $modelType = $params->modelType;


            //coupon列表
            $coupons = $this->couponService->list($modelSpecID, $modelType);

            //使用者的coupon列表
            $userCoupons = $this->memberCouponService->list($params->memberId, $couponId = null);

            $diningCar = $this->diningCarService->find($modelSpecID);
            $isPaid = false;


            if ($diningCar->level >= 1 && $diningCar->expired_at >= Carbon::now()) {
                $isPaid = true;
            }
//            dd($isPaid);

            $result = (new CouponResult())->list($coupons, $userCoupons, $isPaid);


            return $this->success($result);

        } catch (\Exception $e) {

            $errCode = $e->getMessage();
            if ($errCode) {
                return $this->failureCode($errCode);
            }

            return $this->failureCode('E0007');
        }
    }


    /**
     * 根據id取得優惠卷明細
     *
     * @param Request $request
     * @param         $id       優惠卷id
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function detail(Request $request, $id)
    {
        try {


            //coupon詳細資料
            $couponDetail = $this->couponService->find($id);

            $params = new CouponParameter($request);
            //使用者的coupon資訊
            $userCoupons = $this->memberCouponService->list($params->memberId, $id)->first();

            //圖片資訊
            $images = ImageHelper::getImageUrl(ClientType::coupon, $id, 1);

            $result = (new CouponResult())->detail($couponDetail, $userCoupons, $images);

            return $this->success($result);

        } catch (\Exception $e) {

            $errCode = $e->getMessage();
            if ($errCode) {
                return $this->failureCode($errCode);
            }

            return $this->failureCode('E0007');
        }

    }


}


