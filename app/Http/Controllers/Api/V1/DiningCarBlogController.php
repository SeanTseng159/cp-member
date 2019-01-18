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

use App\Result\Ticket\DiningCarBlogResult;

class DiningCarBlogController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;

    public function __construct()
    {

    }

    /**
     * 取動態消息列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $result = (new DiningCarBlogResult)->list();

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
    public function detail(Request $request)
    {
        try {
            $result = (new DiningCarBlogResult)->getBlog(1, true);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
