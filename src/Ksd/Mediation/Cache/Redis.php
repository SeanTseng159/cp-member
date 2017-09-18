<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/16
 * Time: 下午 5:24
 */

namespace Ksd\Mediation\Cache;


use Ksd\Mediation\Helper\EnvHelper;
use Predis\Client;

class Redis
{
    use EnvHelper;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => $this->env('REDIS_SCHEME'),
            'host' => $this->env('REDIS_HOST'),
            'port' => $this->env('REDIS_PORT'),
        ]);
    }

    /**
     * 檢查 key 是否被使用
     * @param $key
     * @return int
     */
    public function exists($key)
    {
        return $this->client->exists($key);
    }

    /**
     * 設定快取
     * @param $key
     * @param $value
     * @param int $expire
     */
    public function set($key, $value, $expire = 3600)
    {
        $this->client->setex($key, $expire, serialize($value));
    }

    /**
     * 根據 key 取得快取資料
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return unserialize($this->client->get($key));
    }

    /**
     * 根據 key 取得快取資料, 若無法取得執行 call function
     * @param $key
     * @param $expire
     * @param $callFunction
     * @return mixed
     */
    public function remember($key, $expire, $callFunction)
    {
        if ($this->exists($key)) {
            return $this->get($key);
        }
        $result = call_user_func($callFunction);
        $this->set($key, $result, $expire);
        return $result;
    }

    /**
     * 根據 key 刪除快取
     * @param $key
     */
    public function delete($key)
    {
        $this->client->del($key);
    }
}