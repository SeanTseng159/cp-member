<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/28
 * Time: 下午 05:24
 */

namespace Ksd\Mediation\Repositories;

use Ksd\Mediation\CityPass\Layout;
use Ksd\Mediation\Config\ProjectConfig;

class LayoutRepository extends BaseRepository
{
    const HOME_KEY = 'layout:home:%s:%s';
    const ADS_KEY = 'layout:ads:%s:%s';
    const EXPLORATION_KEY = 'layout:exploration:%s:%s';
    const CUSTOMIZE_KEY = 'layout:customize:%s:%s';
    const BANNER_KEY = 'layout:banner:%s:%s';

    public function __construct()
    {
        $this->cityPass = new Layout();
        parent::__construct();
    }

    /**
     * 取得首頁資料
     * @return mixed
     */
    public function home()
    {

        $this->cacheKey(self::HOME_KEY);
        return $this->redis->remember($this->genCacheKey(self::HOME_KEY), 3600, function () {
            $cityPass = $this->cityPass->home();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });

    }

    /**
     * 取得廣告左右滿版資料
     * @return mixed
     */
    public function ads()
    {

        return $this->redis->remember($this->genCacheKey(self::ADS_KEY), 3600, function () {
            $cityPass = $this->cityPass->ads();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 取得熱門探索資料
     * @return mixed
     */
    public function exploration()
    {

        return $this->redis->remember($this->genCacheKey(self::EXPLORATION_KEY), 3600, function () {
            $cityPass = $this->cityPass->exploration();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 取得自訂版位資料
     * @return mixed
     */
    public function customize()
    {

        return $this->redis->remember($this->genCacheKey(self::CUSTOMIZE_KEY), 3600, function () {
            $cityPass = $this->cityPass->customize();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 取得底部廣告Banner
     * @return mixed
     */
    public function banner()
    {

        return $this->redis->remember($this->genCacheKey(self::BANNER_KEY), 3600, function () {
            $cityPass = $this->cityPass->banner();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 取得標籤資料
     * @return mixed
     */
    public function info()
    {
            $cityPass = $this->cityPass->info();
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];
    }

    /**
     * 利用目錄id取得目錄資料
     * @param parameter
     * @return mixed
     */
    public function category($parameter)
    {
        $itemId = $parameter->itemId;
        return $this->redis->remember("category:id:$itemId", 3600, function () use ($itemId) {
            $cityPass = $this->cityPass->category($itemId);

            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];

        });
    }

    /**
     * 利用選單id取得選單資料
     * @param parameter
     * @return mixed
     */
    public function menu($parameter)
    {
        $itemId = $parameter->itemId;
        return $this->redis->remember("menu:id:$itemId", 3600, function () use ($itemId) {
            $cityPass = $this->cityPass->category($itemId);
            return [
                ProjectConfig::CITY_PASS => $cityPass
            ];

        });
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