<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:02
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Magento\Cart as MagentoCart;
use Ksd\Mediation\CityPass\Cart as CityPassCart;

class CartRepository extends BaseRepository
{
    const INFO_KEY = 'cart:user:info:%s:%s';
    const DETAIL_KEY = 'cart:user:detail:%s:%s';

    private $memberTokenService;

    public function __construct($memberTokenService)
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
        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {
        return $this->redis->remember($this->genCacheKey(self::DETAIL_KEY), 3600, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->detail();
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->detail();
            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
        });
    }

    /**
     * 商品加入購物車
     * @param $parameters
     */
    public function add($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->add($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->add($item);
            }
        }
        $this->cleanCache();
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     */
    public function update($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->update($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->update($item);
            }
        }
        $this->cleanCache();
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     */
    public function delete($parameters)
    {
        if (!empty($parameters->magento())) {
            $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->delete($parameters->magento());
        } else if(!empty($parameters->cityPass())) {
            foreach ($parameters->cityPass() as $item) {
                $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->delete($item);
            }

        }
        $this->cleanCache();
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