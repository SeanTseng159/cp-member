<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/30
 * Time: 上午 09:42
 */

namespace Ksd\Mediation\CityPass;

use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\OrderResult;
use Log;

class Order extends Client
{
    use EnvHelper;

    /**
     * 取得所有訂單列表
     * @return array
     */
    public function info()
    {

        $result = [];

        try {
            $response = $this->request('GET', 'order/info');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            // TODO:處理抓取不到CITY_PASS API訂單資料
        }

        $data = [];

        if(!empty($result['data'])) {
            foreach ($result['data']['items'] as $item) {
                $order = new OrderResult();
                $order->cityPass($item);
                $data[] = (array)$order;
            }
        }

        return $data;

    }


    /**
     * 取得訂單細項
     * @param $itemId
     * @return array
     */
    public function order($itemId)
    {

        $path = "order/items/$itemId";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        $data = [];
        if(!empty($result['data'])) {
            $order = new OrderResult();
            $order->cityPass($result['data'], true);
            $data[] = $order;
        }
        return $data;

    }


    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return array
     */
    public function search($parameters=null)
    {


        $status = $parameters->status;
//        $orderNo = $parameters->orderNo;
//        $name = $parameters->name;
        $orderData = $parameters->orderData;
        $initDate = $parameters->initDate;
        $endDate = $parameters->endDate;

        $this->putParameters($parameters);



        $response =[];
        try{
            $path = 'order/search';
            if(!empty($status)){
                $this->putQuery('status', $status);

            }else if(!empty($orderData)){
                $this->putQuery('orderNo', $orderData);

            }else if(!empty($initDate)&&!empty($endDate)) {
                $this->putQuery('initDate', $initDate)
                    ->putQuery('endDate', $endDate);
            }
            $response = $this->request('GET', $path);
        }catch (ClientException $e){
            // TODO:抓不到訂單資料
        }

        $body = $response->getBody();
        $result = json_decode($body, true);

        $data = [];

        if(!empty($result['data'])) {
            foreach ($result['data'] as $item) {
                $order = new OrderResult();
                $order->cityPass($item);
                $data[] = (array)$order;
            }
        }
        return $data;


    }


    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return array
     */
    public function find($itemId)
    {

        $url = sprintf('order/detail/%s', $itemId);
        $response = $this->request('GET', $url);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $data = [];

        if(!empty($result['data'])) {
            $order = new OrderResult();
            $order->cityPass($result['data'],true);
            $data[] = $order;
        }

        return $data;

    }

    /**
     * 虛擬 ATM繳款紀錄回傳
     * @param $parameters
     * @return bool
     */
    public function writeoff($parameters)
    {
        $response = $this->putParameters($parameters)->request('POST', 'checkout/writeoff');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 200) ? true : false;
    }

    /**
     * 更新訂單
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        $response = $this->putParameters($parameters)->request('POST', 'payment/feedbackipasspay');
        $result = json_decode($response->getBody(), true);

        Log::debug('=== ctpass update order ===');
        Log::debug(print_r($result, true));

        return ($result['statusCode'] === 201) ? true : false;
    }

}
