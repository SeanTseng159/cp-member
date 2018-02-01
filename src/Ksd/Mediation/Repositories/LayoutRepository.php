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
use Ksd\Mediation\Config\CacheConfig;

class LayoutRepository extends BaseRepository
{
    private $count = 0;

    const HOME_KEY = 'layout:home';
    const ADS_KEY = 'layout:ads';
    const EXPLORATION_KEY = 'layout:exploration';
    const CUSTOMIZE_KEY = 'layout:customize';
    const BANNER_KEY = 'layout:banner';
    const MENU_KEY = 'layout:menu';

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
//        $key = $this->genCacheKey(self::HOME_KEY);
        return $this->redis->remember($this->genCacheKey(self::HOME_KEY), CacheConfig::LAYOUT_TIME, function () {
            return $this->cityPass->home();
        });
/*
        if (!$data && $this->count < 3) {
            $this->cacheKey($key);
            $this->count++;

            return $this->home();
        }
*/
//        return $data;
    }

    /**
     * 取得廣告左右滿版資料
     * @return mixed
     */
    public function ads()
    {

//        return $this->redis->remember($this->genCacheKey(self::ADS_KEY), CacheConfig::LAYOUT_TIME, function () {
            $cityPass = $this->cityPass->ads();
            return $cityPass ;
//        });
    }

    /**
     * 取得熱門探索資料
     * @return mixed
     */
    public function exploration()
    {

//        return $this->redis->remember($this->genCacheKey(self::EXPLORATION_KEY), CacheConfig::LAYOUT_TIME, function () {
            $cityPass = $this->cityPass->exploration();
            return $cityPass ;
//        });
    }

    /**
     * 取得自訂版位資料
     * @return mixed
     */
    public function customize()
    {

//        return $this->redis->remember($this->genCacheKey(self::CUSTOMIZE_KEY), CacheConfig::LAYOUT_TIME, function () {
            $cityPass = $this->cityPass->customize();
            return $cityPass ;
//        });
    }

    /**
     * 取得底部廣告Banner
     * @return mixed
     */
    public function banner()
    {

//        return $this->redis->remember($this->genCacheKey(self::BANNER_KEY), CacheConfig::LAYOUT_TIME, function () {
            $cityPass = $this->cityPass->banner();
            return $cityPass ;
//        });
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
        $itemId = $parameter->id;
//        return $this->redis->remember("category:id:$itemId", CacheConfig::LAYOUT_TIME, function () use ($parameter) {
            $cityPass = $this->cityPass->category($parameter);
            return  $cityPass;
//        });
    }

    /**
     * 取得下拉選單資料
     * @param parameter
     * @return mixed
     */
    public function menu($parameter)
    {
        $itemId = $parameter->id;
//        return $this->redis->remember("menu:id:$itemId", CacheConfig::LAYOUT_TIME, function () use ($itemId) {
            $cityPass = $this->cityPass->menu($itemId);
            return  $cityPass;

//        });
    }

    /**
    * 利用選單id取得商品資料
    * @param parameter
    * @return mixed
    */
    public function maincategory($parameter)
    {
        $itemId = $parameter->id;
//        return $this->redis->remember("maincategory:id:$itemId", CacheConfig::LAYOUT_TIME, function () use ($parameter) {
            return $this->cityPass->maincategory($parameter);
//        });
    }

    /**
     * 利用選單id取得商品資料
     * @param parameter
     * @return mixed
     */
    public function subcategory($parameter)
    {
        $itemId = $parameter->id;
//        return $this->redis->remember("subcategory:id:$itemId", CacheConfig::LAYOUT_TIME, function () use ($parameter) {
            $cityPass = $this->cityPass->subcategory($parameter);
            return  $cityPass;
//        });
    }


    /**
     * 清除快取
     * @return bool
     */
    public function cleanCache()
    {

        $this->cacheKey($this->genCacheKey(self::HOME_KEY),null);

/*
        $categoryId = $this->cityPass->getCategoryId();
        $subCategoryId = $this->cityPass->getSubCategoryId();

        $category = [];
        if (isset($categoryId)) {
            foreach ($categoryId as $id) {
                $category[] = "category:id:" . $id;
            }
            foreach ($category as $item) {
                $this->cacheKey(null,$item);
            }
        }

        $subCategory = [];
        if (isset($subCategoryId)) {
            foreach ($subCategoryId as $id) {
                $subCategory[] = "subcategory:id:" . $id;
            }
            foreach ($subCategory as $item) {
                $this->cacheKey(null,$item);
            }
        }
*/
        return true;

    }

    /**
 * 清除主分頁快取
 * @param $id
 * @return bool
 */
    public function clean($id)
    {

        $key = "category:id:" . $id;
        $this->cacheKey(null,$key);
        $this->genCache($key,$id,"c");


        return true;

    }

    /**
     * 清除主分類快取
     * @param $id
     * @return bool
     */
    public function mainClean($id)
    {

        $key = "maincategory:id:" . $id;
        $this->cacheKey(null,$key);
        $this->genCache($key,$id,"m");


        return true;

    }

    /**
     * 清除子分類快取
     * @param $id
     * @return bool
     */
    public function subClean($id)
    {

        $key = "subcategory:id:" . $id;
        $this->cacheKey(null,$key);
        $this->genCache($key,$id,"s");


        return true;

    }


    /**
     * 根據 key 清除快取
     * @param $key
     * @param $key
     */
    private function cacheKey($key=null,$id=null)
    {
        if(!empty($key)) {
            $index_key = $key;
            $this->redis->delete($this->genCacheKey($index_key));
            $this->home();
        }
        if(!empty($id)) {
            $index_key = $id;
            $this->redis->delete($index_key);
        }

    }

    /**
     * 建立快取 key
     * @param $key
     * @return string
     */
    private function genCacheKey($key)
    {
        return $key;
    }

    /**
     * 重新建立快取
     * @param $key
     * @param $id
     * @param $type
     */
    private function genCache($key,$id,$type)
    {
        if($type === "c") {
            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($id) {
                return $this->cityPass->category($id);
            });
        }
        if($type === "m") {
            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($id) {
                return $this->cityPass->maincategory($id);
            });
        }

        if($type === "s") {
            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($id) {
                return $this->cityPass->subcategory($id);
            });
        }
    }


}
