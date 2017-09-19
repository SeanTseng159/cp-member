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

    public function info()
    {

        $email = $this->getEmail();
        $admintoken = new Client();
        $this->authorization($admintoken->token);

        $response =[];
        try{
            $path = 'V1/orders';
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

        return $data;
    }


    public function order($itemId)
    {
        $admintoken = new Client();
        $this->authorization($admintoken->token);

        $path = "V1/orders/items/$itemId";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $order = new OrderResult();
        $order->magento($result, true);

        return $order;
    }


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

            }else if(!empty($name)){
               $this->putQuery('searchCriteria[filterGroups][3][filters][0][field]', 'name')
                    ->putQuery('searchCriteria[filterGroups][3][filters][0][value]', '%'.$name.'%')
                    ->putQuery('searchCriteria[filterGroups][3][filters][0][condition_type]', 'like');
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

        return $data;
    }

    public function getEmail()
    {
        $response = $this->request('GET', 'V1/customers/me');
        $result = json_decode($response->getBody(), true);
        $email = $result['email'];

        return $email;
    }

}