<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/14
 * Time: 下午 04:44
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Wishlist;
use Ksd\Mediation\Config\ProjectConfig;

class WishlistRepository extends BaseRepository
{
    const INFO_KEY = 'wish:user:info:%s:%s';
    const DETAIL_KEY = 'wish:user:detail:%s:%s';

    public function __construct()
    {
        $this->magento = new Wishlist();
        parent::__construct();
    }

    /**
     * 取得所有收藏列表
     * @return mixed
     */
    public function items()
    {
 //       $this->cleanCache();
        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->items();
            $cityPass = [];
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param $parameter
     */
    public function add($parameter)
    {
        $id = $parameter->no;
        $this->magento->authorization($this->token)->add($id);

    }

    /**
     * 根據商品id 刪除收藏清單商品
     * @param $parameter
     */
    public function delete($parameter)
    {
        $id = $parameter->no;
        $this->magento->authorization($this->token)->delete($id);

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