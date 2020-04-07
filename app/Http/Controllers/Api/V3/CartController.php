<?php
namespace App\Http\Controllers\Api\V3;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use Ksd\Mediation\Parameter\Cart\CartParameter as OldCartParameter;
use Ksd\Mediation\Services\CartMoreService as OldCartService;

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
    protected $oldCartService;


    public function __construct(OldCartService $oldCartService)
    {
        $this->oldCartService = $oldCartService;
    }


    /**
     * 增加商品至購物車
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $parameters = (new CartParameter($request))->moreCars();
        dd($parameters);
        $result = $this->oldCartService->add($parameters);
        
    }//end add

    /**
     * 所有購物車資訊
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mine(Request $request)
    {
        
        $memberID = $this->getMemberId();
        
        $result=$this->oldCartService->mine($memberID);
        
        $data=(new CartMoreResult())->mine($result);
         
    }//end mine




}//end