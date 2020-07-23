<?php

namespace App\Http\Controllers\Api;

use App\Cache\Redis;
use Illuminate\Http\Request;

use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Traits\MemberHelper;
use App\Services\Ticket\OrderService;
use App\Services\GreenPointService;
use App\Services\Ticket\ProductSpecPriceService;

use App\Parameter\GreenPointParameter;


class GreenPointController extends RestLaravelController
{
    use MemberHelper;
    private $orderService;
    private $greenPointService;
    private $productSpecPriceService;

    public function __construct(OrderService $orderService,
                            GreenPointService $greenPointService,
                            ProductSpecPriceService $productSpecPriceService)
    {
        $this->orderService = $orderService;
        $this->greenPointService=$greenPointService;
        $this->productSpecPriceService=$productSpecPriceService;
    }



    public function check(Request $request)
    {
        // try{
            $memberID = $this->getMemberId();
            $code=$request->input('code');
            $data=$this->greenPointService->check($code);
            // if(!$data){
            //     return $this->failure('E0001', '查無此資料');
            // }elseif($data->used==1){
            //     return $this->failure('E0002', '已經領取過');
            // }   
            $prod=$this->productSpecPriceService->find(119);
            
            $cart=(new GreenPointParameter())->cart($prod);
            $params=(new GreenPointParameter())->params();
            
            $test=$this->orderService->create($params, $cart);
            dd($test);
        // }catch(\Exception $e) {
        //     \Log::debug('=== GreenPointController error ===');
        //     return $this->failure('E0000', '錯誤');
        // }
        
    }


}   
