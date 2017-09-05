<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Cart;

class CartRepository extends BaseRepository
{
    const INFO_KEY = 'cart:user:info:%s:%s';
    const DETAIL_KEY = 'cart:user:detail:%s:%s';

    public function __construct()
    {
        $this->magento = new Cart();
        parent::__construct();
    }

    public function info()
    {
        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->info();
            $tpass = [];
            return [
                'magento' => $magento,
                'tpass' => $tpass
            ];
        });
    }

    public function detail()
    {
        return $this->redis->remember($this->genCacheKey(self::DETAIL_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->detail();
            $tpass = [];
            return [
                'magento' => $magento,
                'tpass' => $tpass
            ];
        });
    }

    public function add($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->authorization($this->token)->add($parameters->magento());
        }
        $this->cleanCache();
    }

    public function update($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->authorization($this->token)->update($parameters->magento());
        }
        $this->cleanCache();
    }

    public function delete($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->authorization($this->token)->delete($parameters->magento());
        }
        $this->cleanCache();
    }

    public function cleanCache()
    {
        $this->cacheKey(self::INFO_KEY);
        $this->cacheKey(self::DETAIL_KEY);
    }

    private function cacheKey($key)
    {
        $this->redis->delete($this->genCacheKey($key));
    }

    private function genCacheKey($key)
    {
        $date = new \DateTime();
        return sprintf($key, $this->token,$date->format('Ymd'));
    }
}