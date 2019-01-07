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

use App\Parameter\Ticket\DiningCarParameter;
use App\Services\Ticket\DiningCarCategoryService;
use App\Services\Ticket\DiningCarService;
use App\Result\Ticket\DiningCarCategoryResult;
use App\Result\Ticket\DiningCarResult;

class DiningCarController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $categoryService;

    protected $redis;

    public function __construct(DiningCarService $service, DiningCarCategoryService $categoryService)
    {
        $this->service = $service;
        $this->categoryService = $categoryService;
        // $this->redis = new Redis;
    }

    /**
     * 取餐車分類列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mainCategories()
    {
        $data = $this->categoryService->mainCategory();
        $result = (new DiningCarCategoryResult)->main($data);
        return $this->success($result);
    }

    /**
     * 取營業狀態列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function openStatusList()
    {
        $result = (new DiningCarResult)->getOpenStatusList();
        return $this->success($result);
    }

    /**
     * 取餐車列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $params = (new DiningCarParameter($request))->list();

            $result['page'] = (int) $params['page'];

            $data = $this->service->list($params);
            $result['total'] = $data->total();
            $result['cars'] = (new DiningCarResult)->list($data, $params['latitude'], $params['longitude']);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取餐車地圖
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function map(Request $request)
    {
        try {
            $params = (new DiningCarParameter($request))->map();

            $data = $this->service->map($params);
            $result = (new DiningCarResult)->list($data, $params['latitude'], $params['longitude']);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取餐車詳細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $id)
    {
        try {
            if (!$id) return $this->apiRespFailCode('E0006');

            $params = (new DiningCarParameter($request))->detail();

            $data = $this->service->find($id);
            $result = (new DiningCarResult)->detail($data, $params['latitude'], $params['longitude']);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
