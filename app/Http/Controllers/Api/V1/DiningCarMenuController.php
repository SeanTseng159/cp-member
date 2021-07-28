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

use App\Services\Ticket\MenuService;
use App\Services\Ticket\MenuCategoryService;
use App\Result\Ticket\DiningCarMenuResult;

class DiningCarMenuController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $menuCategoryService;

    public function __construct(MenuService $service, MenuCategoryService $menuCategoryService)
    {
        $this->service = $service;
        $this->menuCategoryService = $menuCategoryService;
    }

    /**
     * 取菜單列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, $diningCarId = 0)
    {
        try {
            $params['diningCarId'] = $diningCarId;

            $data = $this->menuCategoryService->list($params);
            $result = (new DiningCarMenuResult)->list($data);

            //針對menu中可能存在的html字元(ex:&nbsp; &amp;)進行轉換
            //值存在$result[$index]->menus[0]->content
            if($result){
                foreach ($result as $key => $value){
                    foreach($value->menus as $k => $v){
                        $v->content = str_replace('&amp;','&',$value->menus[0]->content);//將&amp; 轉換成 &
                        $v->content = str_replace('&nbsp;',' ',$value->menus[0]->content);//將&nbsp; 轉換成 空白字元
                        }
                }
            }

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取菜單詳細
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $id = 0)
    {
        try {
            $menu = $this->service->find($id);
            $result = (new DiningCarMenuResult)->getMenu($menu, true);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }
}
