<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 03:00
 */

namespace Ksd\Mediation\Magento;

use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Result\OrderResult;

class Order extends Client
{

    /**
     * 取得所有訂單列表
     * @return array
     */
    public function info()
    {

        $email = $this->getEmail();
        $admintoken = new Client();
        $this->authorization($admintoken->token);

        $result =[];
        try{
            $path = 'V1/orders';
            $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email)
                ->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);
        }catch (ClientException $e){
            // TODO:抓不到MAGENTO API訂單資料
        }

        $data = [];
        foreach ($result['items'] as $item) {
            $order = new OrderResult();
            $order->magento($item);
            $data[] = $order;
        }

        return $data;
    }


    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return OrderResult
     */
    public function order($parameter)
    {

        $itemId = $parameter->itemId;
        $id = $parameter->id;
        $email = $this->getEmail();
        $admintoken = new Client();
        $this->authorization($admintoken->token);
        $response =[];
        try{
            $path = 'V1/orders';
            $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email)
                ->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'increment_id')
                ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', $itemId)
                ->request('GET', $path);
        }catch (ClientException $e){
            // TODO:抓不到訂單資料
        }

        $body = $response->getBody();

        $result = json_decode($body, true);


        if(!empty($result['items'][0])) {
            $order = new OrderResult();
            $order->magento($result['items'][0], true);
        }else{
            return null;
        }

        //如有關鍵字搜尋則進行判斷是否有相似字
        if(!empty($id)){
                $count = 0;
                foreach ($order->items as $items) {
                    if(!preg_match("/".$id."/",$items['id'])){
                        array_splice($order->items,$count,1);
                        $count--;
                    }
                    $count++;
                }
        }
        return $order;

    }


    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return OrderResult
     */
    public function search($parameters)
    {
        $email = $this->getEmail();
        $admintoken = new Client();
        $this->authorization($admintoken->token);


        $status = $parameters->status;
        $orderNo = $parameters->orderNo;
        $name = $parameters->name;
        $initDate = $parameters->initDate;
        $endDate = $parameters->endDate;

        $response =[];
        try{
            $path = 'V1/orders';
            if(!empty($status)){
                $this->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'status')
                    ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', $status);

            }else if(!empty($orderNo)){
               $this->putQuery('searchCriteria[filterGroups][2][filters][0][field]', 'increment_id')
                    ->putQuery('searchCriteria[filterGroups][2][filters][0][value]', $orderNo);

            }else if(!empty($initDate)&&!empty($endDate)) {
               $this->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'created_at')
                    ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $initDate)
                    ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'from')
                    ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'created_at')
                    ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $endDate)
                    ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'to');
                    }
           $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'customer_email')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $email)
                ->request('GET', $path);
        }catch (ClientException $e){
            // TODO:抓不到訂單資料
        }

        $body = $response->getBody();
        $data = [];
        $result = json_decode($body, true);


        foreach ($result['items'] as $item) {
            $order = new OrderResult();
            $order->magento($item);
            $data[] = $order;
        }

        //如有關鍵字搜尋則進行判斷是否有相似字
        $flag = false;
        $data1 = [];
        if(!empty($name)){
            foreach ($data as $item) {
                $dataflag = false;
                foreach ($item->items as $items) {
                    if(preg_match("/".$name."/",$items->name)){
                        $flag = true;
                        $dataflag =true;
                    }
                }
                if($dataflag){
                    $data1[] = $item;
                }
            }
        }else{
            $flag = true;
            $data1 = $data;
        }

        return $flag ? $data1 : null;
    }


    /**
     * 取得訂單物流追蹤資訊
     * @return string
     */
    public function getShippingInfo($order_id, $sku)
    {

        $path = 'V1/shipments';
        $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'order_id')
            ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $order_id)
            ->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);


        $data = null;
        if(!empty($result)) {
            foreach ($result['items'] as $items) {
                foreach ($items['items'] as $item) {
                    if (preg_match("/" . $sku . "/", $item['sku'])) {
                        $data = $items['tracks'];
                    }
                }
            }
            return $data;
        }else{

            return null;
        }



    }
    /**
     * 根據 商品編號 取得圖片路徑
     * @param $sku
     * @return array
     */
    public function find($sku)
    {
        $path = "V1/products/$sku/media";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        return $result[0];
    }

    /**
     * 取得使用者email
     * @return string
     */
    public function getEmail()
    {
        $response = $this->request('GET', 'V1/customers/me');
        $result = json_decode($response->getBody(), true);
        $email = $result['email'];

        return $email;
    }

}