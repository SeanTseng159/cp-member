<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\ServiceService;
use App\Result\Ticket\ServiceResult;
use App\Jobs\Mail\PartnerJoin;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\ServiceKey;

use Exception;

class ServiceController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $serviceService;

    protected $redis;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
        $this->redis = new Redis;
    }

    /**
     * 取常見問題
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function qa(Request $request)
    {
        try {
            $result = $this->redis->remember(ServiceKey::QA_KEY, CacheConfig::ONE_MONTH, function () {
                $data = $this->serviceService->faq($this->lang);
                return (new ServiceResult)->faq($data);
            });

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    /**
     * 合作廠商申請
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function partnerJoin(Request $request)
    {
        try {
            $params['company'] = $request->input('company');
            $params['contactWindow'] = $request->input('contactWindow');
            $params['phone'] = $request->input('phone');
            $params['email'] = $request->input('email');
            $params['message'] = $request->input('message');
            $params['taxID'] = $request->input('taxID', '');
            $params['lineID'] = $request->input('lineID', '');

            dispatch(new PartnerJoin($params))->delay(5);

            return $this->success();
        } catch (Exception $e) {
            var_dump($e->getMessage());
            // return $this->failureCode('E9300');
        }
    }
}
