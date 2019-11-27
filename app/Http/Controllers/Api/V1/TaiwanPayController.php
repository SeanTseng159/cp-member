<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

// use App\Services\MenuOrderService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


// use App\Jobs\Mail\OrderPaymentCompleteMail;
// use App\Services\CartService;
// use App\Services\Ticket\OrderService;
// use App\Services\PaymentService;

// use Ksd\Payment\Services\UpDateOrderStatusService;

use App\Traits\CartHelper;


class TaiwanPayController extends RestLaravelController
{
    // use CartHelper;


    // protected $orderService;
    // protected $menuOrderService;
    // protected $blueNewPayService;
    // protected $upDateOrderStatusService;
    // public function __construct(MenuOrderService $menuOrderService,
    //                             OrderService $orderService,
    //                            BlueNewPayService $blueNewPayService,
    //                            UpDateOrderStatusService $upDateOrderStatusService)
    // {
    //     $this->orderService = $orderService;
    //     $this->menuOrderService = $menuOrderService;
    //     $this->blueNewPayService=$blueNewPayService;
    //     $this->upDateOrderStatusService=$upDateOrderStatusService;
    // }

    public function comfirm(Request $request)
    {
        $lidm="20131024T009";
        $authAmt="200";
        $MerchantID="950876543219001";
        $TerminalID="90010001";
        $datetime="20131024141500";
        $math="1qaz2wsx3edc4rfv";

        $word="{$lidm}&{$authAmt}&{$math}&{$MerchantID}&{$TerminalID}&{$datetime}";

        $token=hash('sha256',$word);
        dd($token);

    }

}
