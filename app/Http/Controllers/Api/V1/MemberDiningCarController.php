<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;

use App\Services\Ticket\MemberDiningCarService;
use App\Parameter\Ticket\MemberDiningCarParameter;
use App\Result\Ticket\MemberDiningCarResult;

class MemberDiningCarController extends RestLaravelController
{
    protected $service;

    public function __construct(MemberDiningCarService $service)
    {
        $this->service = $service;
    }

    /**
     * 加入餐車收藏
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, $id)
    {
        try {
            $memberId = $request->memberId;

            $memberDiningCar = $this->service->find($memberId, $id);
            if ($memberDiningCar) return $this->success();

            $result = $this->service->add($memberId, $id);

            return ($result) ? $this->success() : $this->failureCode('E0040');
        } catch (Exception $e) {
            return $this->failureCode('E0040');
        }
    }

    /**
     * 移除餐車收藏
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, $id)
    {
        try {
            $memberId = $request->memberId;

            $result = $this->service->delete($memberId, $id);

            return ($result) ? $this->success() : $this->failureCode('E0041');
        } catch (Exception $e) {
            return $this->failureCode('E0041');
        }
    }

    /**
     * 取收藏列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function favorites(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $params = (new MemberDiningCarParameter($request))->list();

            $data = $this->service->list($memberId, $params);

            $result['page'] = (int) $params['page'];
            $result['total'] = $data->total();
            $result['cars'] = (new MemberDiningCarResult)->list($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
