<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\PaymentMethod;

use App\Cache\Redis;
use App\Cache\Key\CheckoutKey;
use App\Cache\Config as CacheConfig;

use Exception;

class PaymentMethodRepository extends BaseRepository
{
    private $redis;

    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
        $this->redis = new Redis;
    }

    /**
     * 取全部
     * @return mixed
     */
    public function all()
    {
        try {
            return $this->redis->remember(CheckoutKey::PAYMENT_METHOD_KEY, CacheConfig::ONE_DAY, function() {
                return $this->model->where('status', 1)->get();
            });
        } catch (Exception $e) {
            return null;
        }
    }
}
