<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use App\Enum\StoreType;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;

use App\Parameter\Ticket\DiningCarParameter;
use App\Services\Ticket\DiningCarCategoryService;
use App\Services\Ticket\DiningCarService;
use App\Services\Ticket\MemberDiningCarService;
use App\Services\Ticket\KeywordService;
use App\Services\Ticket\MenuService;
use App\Result\Ticket\DiningCarCategoryResult;
use App\Result\Ticket\DiningCarResult;

class DiningCarController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $categoryService;
    protected $redis;
    protected $type = StoreType::DiningCar;
    protected $result ;
    protected $attribute = 'cars';



    public function __construct(DiningCarService $service, DiningCarCategoryService $categoryService,DiningCarResult $result)
    {
        $this->service = $service;
        $this->categoryService = $categoryService;

        $this->service->setStoreType($this->type);
        $this->result = $result;
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
     * @param MemberDiningCarService $memberDiningCarService
     * @param KeywordService $keywordService
     * @param MenuService $menuService
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, MemberDiningCarService $memberDiningCarService,
                         KeywordService $keywordService, MenuService $menuService)
    {
        try {

            $params = (new DiningCarParameter($request))->list();

            // 依關鍵字 找標籤/菜單中的餐車
            if ($params['keyword']) {
                $keywordDiningCarIds = $keywordService->getDiningCarsByKeyword($params['keyword'])
                    ->pluck('dining_car_id')
                    ->toArray();

                $menuDiningCarIds = $menuService->getDiningCarsByKeyword($params['keyword'])->pluck('dining_car_id')->toArray();

                $params['keywordDiningCarIds'] = array_unique(array_merge($keywordDiningCarIds, $menuDiningCarIds));
            }

            $data = $this->service->list($params);


            // 取收藏列表
            $memberDiningCars = ($params['memberId']) ?
                $memberDiningCarService->getAllByMemberId($params['memberId'])
                :
                NULL;

            $result['page'] = (int)$params['page'];
            $result['total'] = $data->total();
            $result[$this->attribute] = $this->result->list($data, $params['latitude'], $params['longitude'], $memberDiningCars);
            return $this->success($result);

        } catch (Exception $e) {

            return $this->failureCode('E0007');
        }
    }

    /**
     * 取餐車地圖
     * @param Request $request
     * @param KeywordService $keywordService
     * @param MenuService $menuService
     * @return \Illuminate\Http\JsonResponse
     */
    public function map(Request $request, KeywordService $keywordService, MenuService $menuService)
    {
        try {
            $params = (new DiningCarParameter($request))->map();

            // 依關鍵字 找標籤/菜單中的餐車
            if ($params['keyword']) {
                $keywordDiningCarIds = $keywordService->getDiningCarsByKeyword($params['keyword'])->pluck('dining_car_id')->toArray();

                $menuDiningCarIds = $menuService->getDiningCarsByKeyword($params['keyword'])->pluck('dining_car_id')->toArray();

                $params['keywordDiningCarIds'] = array_unique(array_merge($keywordDiningCarIds, $menuDiningCarIds));
            }

            $data = $this->service->map($params);
            $result = $this->result->list($data, $params['latitude'], $params['longitude'], NULL);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取餐車詳細
     * @param Request $request
     * @param MemberDiningCarService $memberDiningCarService
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, MemberDiningCarService $memberDiningCarService, $id)
    {
        try {
            if (!$id) return $this->apiRespFailCode('E0006');

            $params = (new DiningCarParameter($request))->detail();
            // 取收藏列表
            $isFavorite = ($params['memberId']) ? $memberDiningCarService->isFavorite($params['memberId'], $id) : false;

            $data = $this->service->find($id, $params['memberId']);
            //$result = (new DiningCarResult)->detail($data, $isFavorite, $params['latitude'], $params['longitude']);
            $result = $this->result->detail($data, $isFavorite, $params['latitude'], $params['longitude']);
            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 取得餐車詳細頁短網址
     * @param string $id 短網址代號
     * @return \Illuminate\Http\JsonResponse
     */
    public function shorterUrl($id)
    {
        try {
            if (!$id) return $this->failureCode('E0006');

            $url = $this->service->getDetailUrlByShorterUrlId($id);
            if (!$url) return $this->failureCode('E0202');

            return $this->success(['url' => $url]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

}
