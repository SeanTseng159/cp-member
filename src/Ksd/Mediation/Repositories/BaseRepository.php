<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:09
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Cache\Redis;

class BaseRepository
{
    protected $redis;
    protected $magento;
    protected $cityPass;
    protected $token;

    public function __construct()
    {
        $this->redis = new Redis();
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}