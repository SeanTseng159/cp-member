<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:57
 */


namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Order as MagentoOrder;
use Ksd\Mediation\CityPass\Order as CityPassOrder;

use Ksd\Mediation\Config\ProjectConfig;

class OrderRepository extends BaseRepository
{
    const INFO_KEY = 'order:user:info:%s:%s';


    public function __construct()
    {
        $this->magento = new MagentoOrder();
        $this->cityPass = new CityPassOrder();
        parent::__construct();
    }

    /**
     * 取得所有訂單列表
     * @return mixed
     */
    public function info()
    {

        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 300, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->info();
            $cityPass = $this->cityPass->info();
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
        $source = $parameter->source;
        return $this->redis->remember("$source:order:id:$itemId", 300, function () use ($source,$parameter) {
            if($source == ProjectConfig::MAGENTO) {
                $this->magento->authorization($this->token);
                $magento = $this->magento->order($parameter);
                return $magento;
            }else {
                $cityPass = $this->cityPass->order($parameter);
                return $cityPass;
            }


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

               case '00': # 待付款
               $parameters->status = "pending";
                   break;
               case '01': # 已完成
               $parameters->status = "complete";
                   break;
               case '02': # 部分退貨
               $parameters->status = "holded";
                   break;
               case '03': # 已退貨
               $parameters->status = "holded";
                   break;
               case '04': # 處理中
               $parameters->status = "processing";
                   break;
           }

            $this->magento->authorization($this->token);
            $magento = $this->magento->search($parameters);
            $cityPass = $this->cityPass->search($parameters);
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