<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Traits\CryptHelper;

use App\Services\Ticket\DiningCarMemberService;

use App\Parameter\Ticket\DiningCarMemberParameter;
use App\Result\Ticket\DiningCarMemberResult;

class DiningCarMemberController extends RestLaravelController
{
    use CryptHelper;

    protected $service;

    public function __construct(DiningCarMemberService $service)
    {
        $this->service = $service;
    }

    /**
     * 加入餐車會員
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $diningCarId = $request->input('diningCarId');

            if (!$diningCarId) return $this->failureCode('E0201');
            $diningCarId = $this->decryptHashId('DiningCar', $diningCarId);

            $isMember = $this->service->isMember($memberId, $diningCarId);
            if ($isMember) return $this->failureCode('A0101');

            $result = $this->service->add($memberId, $diningCarId);
            // 取禮物

            return ($result) ? $this->success(['gift' => null]) : $this->failureCode('E0200');
        } catch (Exception $e) {
            return $this->failureCode('E0200');
        }
    }

    /**
     * 取使用者已加入會員的餐車
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function diningCars(Request $request)
    {
        try {
            $params = (new DiningCarMemberParameter($request))->list();

            $data = $this->service->list($request->memberId, $params);

            $result['page'] = (int) $params['page'];
            $result['total'] = $data->total();
            $result['cars'] = (new DiningCarMemberResult)->list($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
