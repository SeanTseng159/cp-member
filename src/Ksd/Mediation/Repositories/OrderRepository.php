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
use Ksd\Mediation\Config\CacheConfig;
use Ksd\Mediation\Services\MemberTokenService;

use App\Models\PayReceive;

class OrderRepository extends BaseRepository
{
    const INFO_KEY = 'order:user:info:%s:%s';

    private $memberTokenService;
    protected $model;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoOrder();
        $this->cityPass = new CityPassOrder();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;

        $this->model = new PayReceive();
    }

    /**
     * 取得所有訂單列表
     * @return mixed
     */
    public function info()
    {

        return $this->redis->remember($this->genCacheKey(self::INFO_KEY), CacheConfig::TEST_TIME, function () {
            $magento = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
            $cityPass = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();
            $data = array_merge($magento, $cityPass);

            return ($data) ? $this->multi_array_sort($data, 'orderDate') : null;

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
        return $this->redis->remember("$source:order:item_id:$itemId", CacheConfig::TEST_TIME, function () use ($source,$parameter) {
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

            return ($data) ? $this->multi_array_sort($data,'orderDate') : null;
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
        $this->token = $this->memberTokenService->cityPassUserToken();
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

        return $this->redis->remember("$source:order:$id", CacheConfig::TEST_TIME, function () use ($source,$parameters) {
            if ($parameters->source === ProjectConfig::MAGENTO) {
                return $this->magento->find($parameters);
            } else if ($parameters->source === ProjectConfig::CITY_PASS) {
                return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->find($parameters->id);
            }
        });
    }

    /**
     * 根據 id 查訂單
     * @param $parameters
     * @return \Ksd\Mediation\Result\OrderResult
     */
    public function findOneByIpassPay($parameters)
    {
        if ($parameters->source === ProjectConfig::MAGENTO) {
            return $this->magento->find($parameters);
        } else if ($parameters->source === ProjectConfig::CITY_PASS) {
            return $this->cityPass->authorization($parameters->token)->find($parameters->id);
        }
        return null;
    }


    /**
     * 接收ATM繳款通知程式
     * citypass直接把資料回拋，magento驗證成功後再把訂單狀態改為processing
     * @param $parameters
     * @return mixed
     */
    public function writeoff($parameters)
    {

        $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->writeoff($parameters);


        $code               = "abcd1234";
        $verify = md5(
            "merchant_number=".$parameters->merchantnumber.
            "&order_number=".$parameters->ordernumber.
            "&serial_number=".$parameters->serialnumber.
            "&write_off_number=".$parameters->writeoffnumber.
            "&time_paid=".$parameters->timepaid.
            "&payment_type=".$parameters->paymenttype.
            "&amount=".$parameters->amount.
            "&tel=".$parameters->tel.
            $code
        );

            if(strtolower($parameters->hash)!=strtolower($verify)){
                //-- 驗證碼錯誤，資料可能遭到竄改，或是資料不是由ezPay簡單付發送
                $data = [
                    'merchant_number' => $parameters->merchantnumber,
                    'order_number' => $parameters->ordernumber,
                    'serial_number' => $parameters->serialnumber,
                    'write_off_number' => $parameters->writeoffnumber,
                    'time_paid' => $parameters->timepaid,
                    'payment_type' => $parameters->paymenttype,
                    'amount' => $parameters->amount,
                    'tel' => $parameters->tel,
                    'hash' => $parameters->hash,
                    'status' => 0,
                    'memo' => '驗證碼錯誤'
                ];
                $pay = new PayReceive();
                $pay->fill($data)->save();

            }else{
                //-- 驗證正確，請更新資料庫訂單狀態
                $data = [
                    'merchant_number' => $parameters->merchantnumber,
                    'order_number' => $parameters->ordernumber,
                    'serial_number' => $parameters->serialnumber,
                    'writeoff_number' => $parameters->writeoffnumber,
                    'time_paid' => $parameters->timepaid,
                    'payment_type' => $parameters->paymenttype,
                    'amount' => $parameters->amount,
                    'tel' => $parameters->tel,
                    'hash' => $parameters->hash,
                    'status' => 1,
                    'memo' => '驗證正確'
                ];
                $pay = new PayReceive();
                $pay->fill($data)->save();

                $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->writeoff($parameters->ordernumber);


            }

    }

    /**
     * 訂單更新
     * @param $parameters
     * @return  bool
     */
    public function update($token=null, $parameters)
    {
        if ($parameters->source === ProjectConfig::MAGENTO) {
            return $this->magento->update($parameters);
        } else if ($parameters->source === ProjectConfig::CITY_PASS) {
            $order_id = $parameters->order_id;
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserTokenForIpasspay($token, $order_id))->update($parameters);
        }

    }

    /**
     * 資料依日期做排序
     * @param $arr
     *  @param$key
     *  @param $type
     *  @param $short
     * @return array
     */
    public function multi_array_sort($arr,$key,$type=SORT_REGULAR,$short=SORT_DESC){
        if(!empty($arr)) {
            foreach ($arr as $k => $v) {
                $name[$k] = $v[$key];
            }
            array_multisort($name, $type, $short, $arr);

        }
        return $arr;
    }
}
