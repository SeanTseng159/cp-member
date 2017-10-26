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

        if(!empty($result)) {
/*            foreach ($result['data']['items'] as $item) {
                $order = new OrderResult();
                $order->cityPass($item);
                $data[] = $order;
            }
*/
            return (array)$result['data']['items'];
        }else{
            return null;
        }

    }


    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return OrderResult
     */
    public function order($itemId)
    {

        $path = "order/items/$itemId";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);


        if(!empty($result)) {
//            $order = new OrderResult();
//            $order->cityPass($result['data'], true);
            return $result['data'];
        }else{
            return null;
        }
    }


    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return array
     */
    public function search($parameters=null)
    {


        $status = $parameters->status;
        $orderNo = $parameters->orderNo;
        $name = $parameters->name;
        $initDate = $parameters->initDate;
        $endDate = $parameters->endDate;

        $this->putParameters($parameters);



        $response =[];
        try{
            $path = 'order/search';
            if(!empty($status)){
                $this->putQuery('status', $status);

            }else if(!empty($orderNo)){
                $this->putQuery('orderNo', $orderNo);

            }else if(!empty($name)){
                $this->putQuery('name', $name);

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
        if(!empty($result)) {
/*            foreach ($result['data']['items'] as $item) {
                $order = new OrderResult();
                $order->cityPass($item);
                $data[] = $order;
            }
 */           return (array)$result['data']['items'];
        }else{

            return null;
        }

    }


    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return OrderResult
     */
    public function find($itemId)
    {

        $path = "order/items/$itemId";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);


        if(!empty($result)) {
//            $order = new OrderResult();
//            $order->cityPass($result['data'], true);
            return $result['data'];
        }else{
            return null;
        }
    }


}