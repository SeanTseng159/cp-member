<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:57
 */


namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Order;

class OrderRepository extends BaseRepository
{
    const INFO_KEY = 'order:user:info:%s:%s';
    const DETAIL_KEY = 'order:user:detail:%s:%s';

    public function __construct()
    {
        $this->magento = new Order();
        parent::__construct();
    }

    public function info()
    {
        $this->cleanCache();
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

    public function order($parameter)
    {
        $itemId = $parameter->itemId;
        return $this->redis->remember("order:id:$itemId", 3600, function () use ($itemId) {
            $order = $this->magento->order($itemId);
            if (empty($order)) {
                $order = null; //$this->tpass
            }
            return $order;
        });
    }

    public function search($parameters)
    {

            $this->magento->authorization($this->token);
            $magento = $this->magento->search($parameters);
            $tpass = [];
            return [
                'magento' => $magento,
                'tpass' => $tpass
            ];

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