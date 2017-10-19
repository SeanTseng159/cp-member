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
use Ksd\Mediation\Services\MemberTokenService;

class OrderRepository extends BaseRepository
{
    const INFO_KEY = 'order:user:info:%s:%s';

    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoOrder();
        $this->cityPass = new CityPassOrder();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    /**
     * 取得所有訂單列表
     * @return mixed
     */
    public function info()
    {

        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), 300, function () {
            $this->magento->authorization($this->token);
            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();
            $data = array_merge($magento, $cityPass);

            return $this->multi_array_sort($data,'orderDate');

/*            return [
                ProjectConfig::MAGENTO => $magento,
                ProjectConfig::CITY_PASS => $cityPass
            ];
*/
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
        return $this->redis->remember("$source:order:item_id:$itemId", 300, function () use ($source,$parameter) {
            if($source == ProjectConfig::MAGENTO) {
                $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->order($parameter);
                return $magento;
            }else {
                $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->order($parameter->itemId);
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

            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->search($parameters);
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->search($parameters);
            $data = array_merge($magento, $cityPass);

            return $this->multi_array_sort($data,'orderDate');
/*
        return [
            ProjectConfig::MAGENTO => $magento,
            ProjectConfig::CITY_PASS => $cityPass
        ];
*/
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

    /**
     * 根據 id 查訂單
     * @param $parameters
     * @return \Ksd\Mediation\Result\OrderResult
     */
    public function find($parameters)
    {
        $source = $parameters->source;
        $id = $parameters->id;

        return $this->redis->remember("$source:order:$id", 300, function () use ($source,$parameters) {
            if ($parameters->source === ProjectConfig::MAGENTO) {
                return $this->magento->find($parameters);
            } else if ($parameters->source === ProjectConfig::MAGENTO) {
                return $this->cityPass->authorization($this->cityPassUserToken())->find($parameters->id);
            }
        });
    }


    public function multi_array_sort($arr,$key,$type=SORT_REGULAR,$short=SORT_DESC){
        foreach ($arr as $k => $v){
            $name[$k] = $v[$key];
        }
        array_multisort($name,$type,$short,$arr);
        return $arr;
    }
}