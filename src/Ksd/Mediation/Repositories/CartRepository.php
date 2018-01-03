<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Config\CacheConfig;
use Ksd\Mediation\Magento\Cart as MagentoCart;
use Ksd\Mediation\CityPass\Cart as CityPassCart;

use Ksd\Mediation\Services\MemberTokenService;

class CartRepository extends BaseRepository
{
    const INFO_KEY = 'cart:user:info:%s:%s';
    const INFO_KEY_M = 'cart:user:info_magento:%s:%s';
    const INFO_KEY_C = 'cart:user:info_ct_pass:%s:%s';
    const DETAIL_KEY= 'cart:user:detail:%s:%s';
    const DETAIL_KEY_M = 'cart:user:detail_magento:%s:%s';
    const DETAIL_KEY_C= 'cart:user:detail_ct_pass:%s:%s';

    private $memberTokenService;
    private $result = false;

    private $magentoInfo = [];
    private $cityPassInfo = [];
    private $magentoDetail = [];
    private $cityPassDetail = [];

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoCart();
        $this->cityPass = new CityPassCart();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info()
    {

            $this->redis->remember($this->genCacheKey(self::INFO_KEY_M), CacheConfig::CART_TEST_TIME, function () {
            // $this->magento->authorization($this->token);
                $this->magentoInfo = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
            });
            $this->redis->remember($this->genCacheKey(self::INFO_KEY_C), CacheConfig::CART_TEST_TIME, function () {
                $this->cityPassInfo = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();
            });
            return [
                ProjectConfig::MAGENTO => $this->magentoInfo,
                ProjectConfig::CITY_PASS => $this->cityPassInfo
            ];

    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {

        $this->redis->remember($this->genCacheKey(self::DETAIL_KEY_M), CacheConfig::CART_TEST_TIME, function () {
            // $this->magento->authorization($this->token);
            $this->magentoData = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->detail();
        });
        $this->redis->remember($this->genCacheKey(self::DETAIL_KEY_C), CacheConfig::CART_TEST_TIME, function () {
            $this->cityPassData = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail();
        });
        return [
            ProjectConfig::MAGENTO => $this->magentoData,
            ProjectConfig::CITY_PASS => $this->cityPassData
        ];
/*
        return $this->redis->remember($this->genCacheKey(self::DETAIL_KEY), CacheConfig::CART_TEST_TIME, function () {
//            $this->magento->authorization($this->token);
            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->detail();
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail();
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
*/
    }

    /**
     * 商品加入購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->add($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->add($item);
            }
        }
        $this->cleanCache();

        return $this->result;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->update($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->update($item);
            }
        }
        $this->cleanCache();

        return $this->result;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->delete($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->delete($item);
            }

        }
        $this->cleanCache();

        return $this->result;
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
     * 清除快取_magento
     */
    public function cleanCacheMagento()
    {
        $this->cacheKey(self::INFO_KEY_M);
        $this->cacheKey(self::DETAIL_KEY_M);
    }


    /**
     * 清除快取_citypass
     */
    public function cleanCacheCityPass()
    {
        $this->cacheKey(self::INFO_KEY_C);
        $this->cacheKey(self::DETAIL_KEY_C);
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
        $this->token = $this->memberTokenService->cityPassUserToken();
        return sprintf($key, $this->token,$date->format('Ymd'));
    }
}
