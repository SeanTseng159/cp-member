<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\LayoutAppService;
use App\Result\Ticket\LayoutAppResult;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

use Exception;

class LayoutAppController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $layoutAppService;

    protected $redis;

    public function __construct(LayoutAppService $layoutAppService)
    {
        $this->layoutAppService = $layoutAppService;
        $this->redis = new Redis;
    }

    /**
     * 取全部
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        try {
            //$result = $this->redis->remember(LayoutKey::HOME_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutAppService->all();
                $result = (new LayoutAppResult)->all($data, '201832321');
            //});

            return $this->success($result);
        } catch (Exception $e) {

        }
    }
}
