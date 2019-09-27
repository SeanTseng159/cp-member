<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Services\Ticket\ShopBookingService;
use App\Core\Logger;
use App\Result\Ticket\ShopBookingResult;
class ShopBookingController extends RestLaravelController
{

    protected $shopBookingService;

    public function __construct(ShopBookingService $service)
    {
        $this->service = $service;
    }

    public function maxpeople(Request $request, $id){
        try {
            $bookingLimit = $this->service->findBookingLimit($id);
            $data = (new ShopBookingResult())->maxpeople($bookingLimit);
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopBookingController::maxpeople', $e->getMessage());
            return $this->failureCode('E0001');
        }
    }

}