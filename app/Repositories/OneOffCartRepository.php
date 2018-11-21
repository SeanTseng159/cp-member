<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Repositories;

use App\Cache\Redis;
use App\Cache\Key\CartKey;
use App\Cache\Config as CacheConfig;

class OneOffCartRepository extends BaseRepository
{
    protected $redis;

    public function __construct()
    {
        $this->redis = new Redis;
    }

    /**
     * 商品加入購物車
     * @param $memberId
     * @param $data [購物車內容]
     * @return mixed
     */
    public function add($memberId, $data)
    {
        $key = sprintf(CartKey::ONE_OFF_KEY, $memberId);
        return $this->redis->set($key, $data, CacheConfig::ONE_HOUR);
    }

    /**
     * 更新購物車內商品
     * @param $memberId
     * @param $data [購物車內容]
     * @return mixed
     */
    public function update($memberId, $data)
    {
        $key = sprintf(CartKey::ONE_OFF_KEY, $memberId);
        return $this->redis->refesh($key, CacheConfig::ONE_HOUR, function () use ($data) {
            return $data;
        });
    }

    /**
     * 刪除購物車內商品
     * @param $memberId
     * @return mixed
     */
    public function delete($memberId)
    {
        $key = sprintf(CartKey::ONE_OFF_KEY, $memberId);
        return $this->redis->delete($key);
    }

    /**
     * 取購物車商品
     * @param $memberId
     * @return mixed
     */
    public function find($memberId)
    {
        $key = sprintf(CartKey::ONE_OFF_KEY, $memberId);
        return $this->redis->get($key);
    }
}
