<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 03:25
 */

namespace Ksd\Mediation\Repositories;

use Ksd\Mediation\CityPass\MyTicket;
use Ksd\Mediation\Config\ProjectConfig;

class MyTicketRepository extends BaseRepository
{

    const INFO_KEY = 'ticket:info:%s:%s';
    const DETAIL_KEY = 'ticket:detail:%s:%s';
    const EXPLORATION_KEY = 'layout:exploration:%s:%s';
    const CUSTOMIZE_KEY = 'layout:customize:%s:%s';
    const BANNER_KEY = 'layout:banner:%s:%s';

    public function __construct()
    {
        $this->cityPass = new MyTicket();
        parent::__construct();
    }

    /**
     * 取得票券使用說明
     * @return array
     */
    public function help()
    {

            $cityPass = $this->cityPass->help();
            return  $cityPass;

    }

    /**
     * 取得票券列表
     * @param  $parameter
     * @return array
     */
    public function info($parameter)
    {

        $statusId = $parameter->id;
        return $this->redis->remember($this->genCacheKey(self::HOME_KEY), 360, function () use ($statusId) {
            $cityPass = $this->cityPass->info($statusId);
            return $cityPass ;
        });
    }

    /**
     * 利用票券id取得細項資料
     * @param  $parameter
     * @return array
     */
    public function detail($parameter)
    {
        $id = $parameter->id;
        return $this->redis->remember($this->genCacheKey(self::DETAIL_KEY),360, function () use ($id) {
            $cityPass = $this->cityPass->detail($id);
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 利用票券id取得使用紀錄
     * @return array
     */
    public function record($parameter)
    {

        return $this->redis->remember($this->genCacheKey(self::EXPLORATION_KEY), 3600, function () {
            $cityPass = $this->cityPass->exploration();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 轉贈票券
     * @param parameter
     */
    public function category($parameters)
    {
        $itemId = $parameters->itemId;

        $this->cityPass->category($itemId);
        $this->cleanCache();
    }

    /**
     *  轉贈票券退回
     * @param parameter
     */
    public function menu($parameter)
    {
        $itemId = $parameter->itemId;

        $this->cityPass->category($itemId);
        $this->cleanCache();
    }


    /**
     * 清除快取
     */
    public function cleanCache()
    {

        $this->cacheKey(self::HOME_KEY);
        $this->cacheKey(self::ADS_KEY);
        $this->cacheKey(self::EXPLORATION_KEY);
        $this->cacheKey(self::CUSTOMIZE_KEY);
        $this->cacheKey(self::BANNER_KEY);

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