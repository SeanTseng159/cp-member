<?php
/**
 * User: Lee
 * Date: 2017/8/16
 * Time: 下午 5:24
 */

namespace App\Cache;


use Illuminate\Support\Facades\App;
use Predis\Client;
use Cache;

class Redis
{
    public $lang;

    /**
     * 檢查 key 是否被使用
     * @param $key
     * @return int
     */
    public function exists($key)
    {
        return Cache::has($this->i18nKey($key));
    }

    /**
     * 設定快取
     * @param $key
     * @param $value
     * @param int $expire
     */
    public function set($key, $value, $expire = 3600)
    {
        Cache::put($this->i18nKey($key), $value, $expire);
    }

    /**
     * 根據 key 取得快取資料
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return Cache::get($this->i18nKey($key));
    }

    /**
     * 根據 key 取得快取資料, 若無法取得執行 call function
     * @param $key
     * @param $expire
     * @param $callFunction
     * @return mixed
     */
    public function remember($key, $expire, $callFunction)
    {
        /*if ($this->exists($key) && !$isRefresh) {
            return $this->get($key);
        }
        $result = call_user_func($callFunction);
        $this->set($key, $result, $expire);
        return $result;*/

        return Cache::remember($this->i18nKey($key), $expire, $callFunction);
    }

    /**
     * 根據 key 刪除快取
     * @param $key
     */
    public function delete($key)
    {
        Cache::forget($this->i18nKey($key));
    }

    /**
     * 根據 key 重新建立
     * @param $key
     * @param $expire
     * @param $callFunction
     * @param $key
     */
    public function refesh($key, $expire, $callFunction)
    {
        $this->delete($key);
        return $this->remember($key, $expire, $callFunction);
    }

    /**
     * 取得 i18n key
     * @param $key
     * @return string
     */
    public function i18nKey($key)
    {
        if (empty($this->lang)) {
            $local = App::getLocale();
        }
        return sprintf('%s:%s:%s', env('APP_ENV'), $local, $key);
    }
}
