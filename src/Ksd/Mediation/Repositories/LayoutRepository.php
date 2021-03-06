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
use Ksd\Mediation\Cache\Key\LayoutKey;

class LayoutRepository extends BaseRepository
{
    private $count = 0;
    private $page_limit = 20;

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
        return $this->redis->remember($this->genCacheKey(LayoutKey::HOME_KEY), CacheConfig::LAYOUT_TIME, function () {
            return $this->cityPass->home();
        });
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
        return $this->redis->remember("category:id:$itemId", CacheConfig::LAYOUT_TIME, function () use ($parameter) {
            $cityPass = $this->cityPass->category($parameter);
            return  $cityPass;
        });
    }

    /**
     * 取得下拉選單資料
     * @param parameter
     * @return mixed
     */
    public function menu($parameter)
    {
        $itemId = $parameter->id;
        $itemId = $itemId ?: 'all';

        $key = $this->genCacheKey(LayoutKey::MENU_KEY, $itemId);
        return $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($itemId) {
            $cityPass = $this->cityPass->menu($itemId);
            return  $cityPass;
        });
    }

    /**
    * 利用選單id取得商品資料
    * @param parameter
    * @return mixed
    */
    public function maincategory($parameter)
    {
        $id = $parameter->id;
        $page = $parameter->page = $parameter->page ?: '1';
        return $this->genCategoryCache('main', "maincategory:id:$id:$page", $id, $page);
    }

    /**
     * 利用選單id取得商品資料
     * @param parameter
     * @return mixed
     */
    public function subcategory($parameter)
    {
        $id = $parameter->id;
        $page = $parameter->page = $parameter->page ?: '1';
        return $this->genCategoryCache('sub', "subcategory:id:$id:$page", $id, $page);
    }


    /**
     * 清除快取
     * @return bool
     */
    public function cleanCache()
    {
        $this->cacheKey($this->genCacheKey(LayoutKey::HOME_KEY));
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
        $page = 1;
        $key = "maincategory:id:$id:$page";

        // 刪除快取，重新建立
        $this->redis->delete($key);
        $maincategory = $this->genCategoryCache('main', $key, $id, $page);

        if ($maincategory) {
            // 計算總頁數，並重新建立快取
            $page = ceil($maincategory['total'] / $this->page_limit);
            for ($i = 2; $i <= $page; $i++) {
                $page = $i;
                $key = "maincategory:id:$id:$page";
                $this->redis->delete($key);
                $this->genCategoryCache('main', $key, $id, $page);
            }
        }

        return true;
    }

    /**
     * 清除子分類快取
     * @param $id
     * @return bool
     */
    public function subClean($id)
    {
        $page = 1;
        $key = "subcategory:id:$id:$page";

        // 刪除快取，重新建立
        $this->redis->delete($key);
        $subcategory = $this->genCategoryCache('sub', $key, $id, $page);

        if ($subcategory) {
            // 計算總頁數，並重新建立快取
            $page = ceil($subcategory['total'] / $this->page_limit);
            for ($i = 2; $i <= $page; $i++) {
                $page = $i;
                $key = "subcategory:id:$id:$page";
                $this->redis->delete($key);
                $this->genCategoryCache('sub', $key, $id, $page);
            }
        }

        return true;
    }

    /**
     * 產生分類快取
     * @param $id
     * @return bool
     */
    private function genCategoryCache($type, $key, $id, $page)
    {
        return $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($type, $id, $page) {
            $parameter = new \stdClass;
            $parameter->id = $id;
            $parameter->page = $page;

            if ($type === 'main') return $this->cityPass->maincategory($parameter);
            elseif ($type === 'sub') return $this->cityPass->subcategory($parameter);

            return [];
        });
    }

    /**
     * 清除選單快取
     * @param $id
     * @return bool
     */
    public function cleanMenu()
    {
        $allkey = $this->genCacheKey(LayoutKey::MENU_KEY, 'all');
        
        // 刪除快取，重新建立
        $this->redis->delete($allkey);
        $menus = $this->redis->remember($allkey, CacheConfig::LAYOUT_TIME, function () {
            return $this->cityPass->menu();
        });

        foreach ($menus as $menu) {
            foreach ($menu as $m) {
                $id = $m['id'];
                $key = $this->genCacheKey(LayoutKey::MENU_KEY, $id);

                $this->redis->delete($key);
                $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($id) {
                    return $this->cityPass->menu($id);
                });
            }
        }

        return false;
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
    private function genCacheKey($key, $id = null)
    {
        return ($id) ? sprintf($key, $id) : $key;
    }

    /**
     * 重新建立快取
     * @param $key
     * @param $id
     * @param $type
     */
    private function genCache($key,$id,$type)
    {

        $parameter = new \stdClass();
        $parameter->id = $id;
        $parameter->page = "";

        if($type === "c") {

            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($parameter) {
                return $this->cityPass->category($parameter);
            });
        }
        if($type === "m") {
            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($parameter) {
                return $this->cityPass->maincategory($parameter);
            });
        }

        if($type === "s") {
            $this->redis->remember($key, CacheConfig::LAYOUT_TIME, function () use ($parameter) {
                return $this->cityPass->subcategory($parameter);
            });
        }
    }


}
