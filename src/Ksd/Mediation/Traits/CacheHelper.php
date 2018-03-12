<?php
/**
 * User: lee
 * Date: 2018/03/08
 * Time: 上午 9:42
 */

namespace Ksd\Mediation\Traits;

use Ksd\Mediation\Cache\Redis;

trait CacheHelper
{
    protected $redis;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    /**
     * 根據 key 清除快取
     * @param $key
     */
    public function deleteCache($key)
    {
        if ($key) $this->redis->delete($key);
    }
}
