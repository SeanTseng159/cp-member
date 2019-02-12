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

use App\Parameter\Ticket\DiningCarBlogParameter;
use App\Services\Ticket\NewsfeedService;
use App\Result\Ticket\DiningCarBlogResult;

class DiningCarBlogController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;

    public function __construct(NewsfeedService $service)
    {
        $this->service = $service;
    }

    /**
     * 取動態消息列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $diningCarId = 0)
    {
        try {
            $params = (new DiningCarBlogParameter($request))->list();
            $params['diningCarId'] = $diningCarId;

            $data = $this->service->list($params);

            $result['page'] = (int) $params['page'];
            $result['total'] = $data->total();
            $result['blogs'] = (new DiningCarBlogResult)->list($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取動態消息詳細
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $id = 0)
    {
        try {
            $data = $this->service->find($id);
            $result = (new DiningCarBlogResult)->getNewsFeed($data, true);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
