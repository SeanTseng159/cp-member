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
use App\Services\Ticket\DiningCarService;
use App\Result\Ticket\DiningCarResult;

class DiningCarController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;

    protected $redis;

    public function __construct(DiningCarService $service)
    {
        $this->service = $service;
        // $this->redis = new Redis;
    }

    /**
     * 取餐車列表
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $page = 1)
    {
        try {
            $params = (new DiningCarParameter($request))->list();

            $data = $this->service->list();
            $result = (new DiningCarResult)->list($data, $params->latitude, $params->longitude);

            return $this->success($result);
        } catch (Exception $e) {
            // var_dump($e->getMessage());
            return $this->success();
        }
    }
}
