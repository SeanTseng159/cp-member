<?php
namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Cart\ProductParameterv3;
use Ksd\Mediation\Parameter\Cart\CartParameter as OldCartParameter;
use Ksd\Mediation\Services\CartMoreService;

// new
use App\Parameter\CartParameter;


use App\Result\CartMoreResult;

use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;
use App\Traits\MemberHelper;

class CartController extends RestLaravelController{
    use CartHelper;
    use MemberHelper;
    protected $cartService;


    public function __construct(CartMoreService $cartService)
    {
        $this->cartService = $cartService;
    }


    /**
     * 增加商品至購物車
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        //導入source
        $source = $request->input('source');
        //整理參數
        $parameters = new ProductParameterv3();
        $parameters->laravelRequest($request);
        //送去CI專案 進行寫入DB 
        $result = $this->cartService->add($parameters);
        //成功寫入
        if ($result['statusCode'] === 201) {
            return $this->success();
        }
        else {
            return (isset($result['message'])) ? $this->failure('E9999', $result['message']) : $this->failure('E0003', '更新失敗');
        }
    }//end add

    /**
     * 所有購物車資訊
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mine(Request $request)
    {
        
        $memberID = $this->getMemberId();
        
        $cartItems=$this->cartService->getCartByMemberId($memberID);
        
        //如果是空的化回傳data=null
        if(empty( $cartItems)){
            return $this->success();
        }else{
            $number='';
            foreach($cartItems as $item){
                $number=$number.','.$item->cart_item_type;
            }
        }   
        //$cartNumber= 1,10,100 放入dining_car_id
        $cartNumber=substr($number,1,strlen($number));
        //將資料送去給CI專案吧
        $data=$this->cartService->mine(['cartNumber'=>$cartNumber]);
        
        return $this->success($data);
         
    }//end mine




}//end