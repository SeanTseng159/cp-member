<?php
/**
 * User: lee
 * Date: 2018/12/07
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Result\Activity\MarketResult;

// use App\Cache\Redis;
// use App\Cache\Config as CacheConfig;
// use App\Cache\Key\ServiceKey;

use Exception;

class MarketController extends RestLaravelController
{
    protected $lang = 'zh-TW';

    protected $redis;

    public function __construct()
    {
        // $this->redis = new Redis;
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
            /*$result = $this->redis->remember(ServiceKey::QA_KEY, CacheConfig::ONE_MONTH, function () {
                $data = $this->serviceService->faq($this->lang);
                return (new ServiceResult)->faq($data);
            });*/

            $result = (new MarketResult)->get($id);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }
}
