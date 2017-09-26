<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:57
 */


namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Order;
use Ksd\Mediation\Config\ProjectConfig;

class OrderRepository extends BaseRepository
{
    const INFO_KEY = 'order:user:info:%s:%s';
    const DETAIL_KEY = 'order:user:detail:%s:%s';

    public function __construct()
    {
        $this->magento = new Order();
        parent::__construct();
    }

    /**
     * 取得所有訂單列表
     * @return mixed
     */
    public function info()
    {
        $this->cleanCache();
        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->info();
            $cityPass = [];
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 根據訂單id 取得訂單細項資訊
     * @param parameter
     * @return mixed
     */
    public function order($parameter)
    {
        $itemId = $parameter->itemId;
        return $this->redis->remember("order:id:$itemId", 3600, function () use ($itemId) {
            $magento = $this->magento->order($itemId);
            $cityPass = [];
            if (empty($order)) {
                $order = null; //$this->tpass
            }
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];

        });
    }

    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return mixed
     */
    public function search($parameters)
    {
           switch($parameters->status){

               case '0': # 待付款
               $parameters->status = "pending";
                   break;
               case '1': # 已完成
               $parameters->status = "complete";
                   break;
               case '2': # 部分退貨
               $parameters->status = "holded";
                   break;
               case '3': # 已退貨
               $parameters->status = "holded";
                   break;
               case '4': # 處理中
               $parameters->status = "processing";
                   break;
           }
            $this->magento->authorization($this->token);
            $magento = $this->magento->search($parameters);
            $cityPass = [];
        return [
            ProjectConfig::MAGENTO => $magento,
            ProjectConfig::CITY_PASS => $cityPass
        ];

    }

    /**
     * 清除快取
     */
    public function cleanCache()
    {
        $this->cacheKey(self::INFO_KEY);
        $this->cacheKey(self::DETAIL_KEY);
    }

    /**
     * 根據 key 清除快取
     * @param $key
     */
    private function cacheKey($key)
    {
        $this->redis->delete($this->genCacheKey($key));
    }

    /**
     * 建立快取 key
     * @param $key
     * @return string
     */
    private function genCacheKey($key)
    {
        $date = new \DateTime();
        return sprintf($key, $this->token,$date->format('Ymd'));
    }
}