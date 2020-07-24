<?php

namespace App\Http\Controllers\Api;

use App\Cache\Redis;
use Illuminate\Http\Request;

use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Traits\MemberHelper;
use App\Services\Ticket\OrderService;
use App\Services\GreenPointService;
use App\Services\Ticket\ProductSpecPriceService;
use Ksd\Mediation\Services\CheckoutService;

use App\Parameter\GreenPointParameter;


class GreenPointController extends RestLaravelController
{
    use MemberHelper;
    private $orderService;
    private $greenPointService;
    private $productSpecPriceService;
    private $checkoutService;
    public function __construct(OrderService $orderService,
                            GreenPointService $greenPointService,
                            ProductSpecPriceService $productSpecPriceService,
                            CheckoutService $checkoutService)
    {
        $this->orderService = $orderService;
        $this->greenPointService=$greenPointService;
        $this->productSpecPriceService=$productSpecPriceService;
        $this->checkoutService=$checkoutService;
    }



    public function check(Request $request)
    {
        try{
            $memberID = $this->getMemberId();
            $code=$request->input('code');
            $prodSpecPriceId=$request->input('priceId');
            $data=$this->greenPointService->check($code);

            // if(!$data){
            //     return $this->failure('E0001', '查無此資料');
            // }elseif($data->used==1){
            //     return $this->failure('E0002', '已經領取過');
            // }elseif($data->prodSpecPriceId!=$prodSpecPriceId){
            //     return $this->failure('E0003', '錯誤的領取');
            // }   
            $prod=$this->productSpecPriceService->find($prodSpecPriceId);
            
            $cart=(new GreenPointParameter())->cart($prod);
            $params=(new GreenPointParameter())->params();
            
            \DB::beginTransaction();
            $orderData=$this->orderService->create($params, $cart);
            $this->greenPointService->update($data->id,['member_id'=>$memberID,'used'=>1]);
            // dd($orderData['order_no']);
            $parameters= [
                'orderNo' => $orderData['order_no'],
                'amount'   => 1,
                'status'   => 1
            ];
            $this->checkoutService->feedbackPay($parameters);
            \DB::commit();
            return $this->success();

        }catch(\Exception $e) {
            \DB::rollBack();
            \Log::debug('=== GreenPointController error ===');
            dd($e);
            return $this->failure('E0000', '錯誤');
        }
        
    }


}   
