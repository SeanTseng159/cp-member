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
            $version = $this->getAllCacheKey();
            $key = sprintf(LayoutKey::SERVICE_APPS_VERSION_KEY, $version);
            $result = $this->redis->remember($key, CacheConfig::ONE_MONTH, function () use ($version) {
                $data = $this->layoutAppService->all();
                return (new LayoutAppResult)->all($data, $version);
            });

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    /**
     * 取key
     */
    private function getAllCacheKey()
    {
        $version = $this->redis->get(LayoutKey::SERVICE_APPS_KEY);

        if (!$version) {
            $version = time();
            $this->redis->set(LayoutKey::SERVICE_APPS_KEY, $version, CacheConfig::ONE_MONTH);
        }

        return $version;
    }
}
