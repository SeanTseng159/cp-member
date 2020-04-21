<?php
namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Cart\ProductParameterv3;
use Ksd\Mediation\Parameter\Cart\CartParameter as OldCartParameter;
use Ksd\Mediation\Services\CartMoreService;
use App\Core\Logger;
// new
use App\Parameter\CartParameter;


use App\Result\CartMoreResult;

use App\Traits\CartHelper;
use Exception;
use App\Exceptions\CustomException;
use App\Traits\MemberHelper;
use Psy\TabCompletion\Matcher\FunctionDefaultParametersMatcher;
use ReflectionFunctionAbstract;

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
        try{
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
            }//end if

        } catch (\Exception $e) {
            Logger::error('v3/CartController/add',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
        
    }//end add

    /**
     * 所有購物車資訊
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mine(Request $request)
    {
        try{
            $cartNumber=$request->query('cartNumber');
            //將資料送去給CI專案吧
            $data=$this->cartService->mine(['cartNumber'=>$cartNumber]);
            
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('v3/CartController/mine',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
         
    }//end mine

    public function detail(Request $request){
        try{
            //取出會員資訊
            $memberID = $this->getMemberId();
            //取出購物車號碼
            $cartNumber=$this->getCartsNumber($memberID);
            $data=$this->cartService->mine(['cartNumber'=>$cartNumber]);


            //清空購物車後僅有items被清空　太多資料留下來　清掉不必要的資料
            $result=[];
            foreach($data as $item){   
                if(!empty($item->items)){
                    $result[]=$item;
                }
            }
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('v3/CartController/mine',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
    }//end detail


    public function info(Request $request){
        try{

            $memberID = $this->getMemberId();
            //將資料送去給CI專案吧
            $cartNumber=$this->getCartsNumber($memberID);
            $data=$this->cartService->info(['cartNumber'=>$cartNumber]);
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('v3/CartController/into',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
    }//end info

    public function update(Request $request){
        try{

            $memberID = $this->getMemberId();
            //整理參數
            $parameters = new ProductParameterv3();
            $parameters->laravelRequest($request);
            //將資料送去給CI專案吧
            $result = $this->cartService->update($parameters);
            //成功寫入
            if ($result['statusCode'] === 202) {
                return $this->success();
            }
            else {
                return (isset($result['message'])) ? $this->failure('E9999', $result['message']) : $this->failure('E0003', '更新失敗');
            }//end if
            

        } catch (\Exception $e) {
            Logger::error('v3/CartController/update',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
    }//end update


    public function delete(Request $request){
        try{

            $memberID = $this->getMemberId();
            //整理參數
            $parameters = new ProductParameterv3();
            $parameters->laravelRequest($request);
            //將資料送去給CI專案吧
            $result = $this->cartService->delete($parameters);
            //成功寫入
            if ($result['statusCode'] === 203) {
                return $this->success();
            }
            else {
                return (isset($result['message'])) ? $this->failure('E9999', $result['message']) : $this->failure('E0004', '刪除失敗');
            }//end if
            

        } catch (\Exception $e) {
            Logger::error('v3/CartController/delete',$e->getMessage());
            return $this->failure('E0004', $e->getMessage());
        }//try
    }//end update



    //透過memberID找出你自己全部的購物車號碼
    private function getCartsNumber($memberID){
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
        return $cartNumber;
    }//getCartsNumber

}//end