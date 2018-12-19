<?php
/**
 * User: lee
 * Date: 2018/12/07
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\PromotionService;
use App\Result\Activity\MarketResult;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\ActivityKey;

use Exception;

class MarketController extends RestLaravelController
{
    protected $lang = 'zh-TW';

    protected $redis;

    protected $service;

    public function __construct(PromotionService $service)
    {
        $this->service = $service;
        $this->redis = new Redis;
    }

    /**
     * 取常見問題
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request, $id)
    {
        try {
            /*$key = sprintf(ActivityKey::MARKET_KEY, $id);
            $result = $this->redis->remember($key, CacheConfig::ONE_MONTH, function () use ($id) {
                $market = $this->service->find($id);
                return (new MarketResult)->get($market);
            });*/

            $market = $this->service->find($id);
            $result = (new MarketResult)->get($market);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }
}
