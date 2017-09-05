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

    public function exists($key)
    {
        return $this->client->exists($key);
    }

    public function set($key, $value, $expire = 3600)
    {
        $this->client->setex($key, $expire, serialize($value));
    }

    public function get($key)
    {
        return unserialize($this->client->get($key));
    }

    public function remember($key, $expire, $callFunction)
    {
        if ($this->exists($key)) {
            return $this->get($key);
        }
        $result = call_user_func($callFunction);
        $this->set($key, $result, $expire);
        return $result;
    }

    public function delete($key)
    {
        $this->client->del($key);
    }
}