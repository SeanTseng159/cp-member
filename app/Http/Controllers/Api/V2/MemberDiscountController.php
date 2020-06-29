<?php

namespace App\Http\Controllers\Api\V2;

use App\Cache\Redis;
use Ksd\Mediation\Services\CartMoreService;
use App\Core\Logger;

use Carbon\Carbon;

use App\Services\Ticket\MemberDiscountService;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;
use App\Traits\MemberHelper;
use App\Traits\CartHelper;
use App\Result\Ticket\MemberDiscountResult;
use Illuminate\Support\Facades\Hash;
use App\Services\DiscountCodeService;
class MemberDiscountController extends RestLaravelController
{
    use CartHelper;
    use MemberHelper;

    protected $cartService;
    protected $service;
    protected $discountCodeService;

    public function __construct(CartMoreService $cartService,
                                MemberDiscountService  $service,
                                DiscountCodeService $discountCodeService
                                )
    {
        $this->cartService = $cartService;
        $this->service=$service;
        $this->discountCodeService=$discountCodeService;
  
    }

    
    /**
     *  //優惠卷列表
     * @param Request $request cartNumber
     * @return JsonResponse
     */
    public function listCanUsed(Request $request)
    {
        try{

            //取出會員資訊
            $memberID = $this->getMemberId();
            //取出購物車號碼
            $cartNumber=$request->query('cartNumber');            
            //跑api拿出購物車內的商品送去給CI專案吧
            $cartItems=$this->cartService->mine(['cartNumber'=>$cartNumber]); 
            //拿出列表優惠倦
            $member_discounts=$this->service->listCanUsed($memberID);
            //判斷得程式
            $result=(new MemberDiscountResult)->listCanUsed($member_discounts,$cartItems,$memberID);
            
            return $this->success($result);
            

            //拿出 dicount_codes
        }catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try

    }//end list


     /**
     *  //商品可以領取的優惠卷
     * @param Request $request 　prodId
     * @return JsonResponse
     */
    public function listByProd(Request $request,$prodId)
    {
        try{
            
            $memberID = $this->getMemberId();
            
            // //拿出自己優惠倦
            if(empty($memberID)){
                $member_discounts=null;
            }else{
                //拿出列表優惠倦
                $member_discounts=$this->service->listProdDiscount($memberID);
            }
            //拿出可以用的優惠倦
            $discountCodes=$this->discountCodeService->allEnableDiscountByProd($prodId);
            
            //判斷得程式
            foreach($discountCodes as $itemDiscount){
                //是否有這張discount
                if(collect($member_discounts)->contains('discount_code_id',$itemDiscount->discount_code_id)){
                    $ownStatus=true;
                }else{
                    $ownStatus =false;
                }
                $resultObj=new \stdClass;
                $resultObj->id= $itemDiscount->discount_code_id;
                $resultObj->name= $itemDiscount->discount_code_name;
                $resultObj->value= $itemDiscount->discount_code_value;
                $resultObj->ownStatus= $ownStatus;
          
                $result[]=$resultObj;


            }//end foreach
            
            return $this->success($result);
            
            

        }catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try

    }//end list


    /**
     *  //領取優惠卷
     * @param Request $request ['2019test','XXXXX'] 
     * @return JsonResponse
     */
    public function getByCode(Request $request){
        try{
            $discountValueArray=$request->input('discountCode');
            if(empty($discountValueArray)){
                return $this->failure('E0001', '沒有折價卷代碼');
            }
            $memberID = $this->getMemberId();

            foreach($discountValueArray as $discountValue) {
                
                //拿出可以用的優惠倦
                $discountCodes=$this->discountCodeService->getEnableDiscountByCode($discountValue);
                //判斷是否有優惠倦 可是沒了 或者 代碼有錯誤
                if(empty($discountCodes)){
                    $output=$this->failure('E0001', '領取完畢或代碼錯誤');
                }else{
                    
                    //寫入DB併檢查是否已經領取過
                    $result=$this->service->createAndCheck(['discount_code_id'=>$discountCodes->discount_code_id,
                                            'member_id'=>  $memberID,        
                                            'used' =>0,
                                            'status'=>1]);
                    if($result){
                        $output=$this->success();
                    }else{
                        $output=$this->failure('E0001', '已經領取過');
                    }
                }

                
            
            }
            if(count($discountValueArray)==1){
                return $output;
            }else{
                return $this->success();
            }
            
            

        }catch (\Exception $e) {
            // Logger::error('v2/MemberDiscountController/list',$e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }//try
    
    }

    /**
     *  //領取優惠卷
     * @param Request $request  current   used  disabled
     * @return JsonResponse
     */
    public function list(Request $request,$func){
        $memberID = $this->getMemberId();
        
        
        switch ($func)
        {
            
            case 'current':
            //可使用 valid
                $data=$this->service->current($memberID);
                break;
            //已經使用 used 
            case 'used':
                $data=$this->service->used($memberID);
                break;
            //以失效 disabled
            case 'disabled':
                $data=$this->service->disabled($memberID);
                
                break;
            case 'all':
                 $data1=$this->service->current($memberID);
                 $data2=$this->service->used($memberID);
                 $data3=$this->service->disabled($memberID);
                 $result1=(new MemberDiscountResult)->list($data1,'current');
                 $result2=(new MemberDiscountResult)->list($data2,'used');
                 $result3=(new MemberDiscountResult)->list($data3,'disabled');
                 $result=array_merge($result1,$result2,$result3);
                 return $this->success($result);
            default:
                return $this->failure('E0001', '錯誤代碼'); 
            
        }//end switch

        if(empty($data)){
            return $this->success();
        }else{
            $result=(new MemberDiscountResult)->list($data,$func);
        }

    }//end







    
}
