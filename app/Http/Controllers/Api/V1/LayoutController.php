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
use App\Result\Ticket\LayoutResult;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

use Exception;

class LayoutController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $layoutService;

    protected $redis;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
        $this->redis = new Redis;
    }

    /**
     * 取首頁資料
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request)
    {
        try {
            $result = $this->redis->remember(LayoutKey::HOME_KEY, CacheConfig::LAYOUT_TIME, function () {
                $data = $this->layoutService->home($this->lang);
                return (new LayoutResult)->home($data);
            });

            return $this->success($result);
        } catch (Exception $e) {
            $result = new \stdClass;
            $result->slide = [];
            $result->banner = [];
            $result->explorations = [];
            $result->customizes = [];
            return $this->success($result);
        }
    }
}
