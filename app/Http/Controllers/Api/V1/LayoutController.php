<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\LayoutService;
// use App\Result\Ticket\LayoutResult;

// use App\Services\MagentoProductService;
// use App\Result\MagentoProductResult;

class LayoutController extends RestLaravelController
{
    protected $layoutService;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    /**
     * 取首頁資料
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request)
    {
        $data = $this->layoutService->home();
        var_dump($data);
        //return $this->success($result);
    }
}
