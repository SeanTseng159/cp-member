<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 03:26
 */

namespace Ksd\Mediation\CityPass;

use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\LayoutResult;

class MyTicket extends Client
{
    use EnvHelper;


    /**
     * 取得票券使用說明
     * @return array
     */
    public function help()
    {

        $path = "ticket/help";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if(!empty($result['data'])) {
            return $result['data'];
        }else{
            return null;
        }

    }

    /**
     * 取得所有訂單列表
     * @param $statusId
     * @return array
     */
    public function info($statusId)
    {

        $path = "ticket/info/".$statusId;

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if(!empty($result['data'])) {
            return $result['data'];
        }else{
            return null;
        }
    }

    /**
     * 利用票券id取得細項資料
     * @param $id
     * @return array
     */
    public function detail($id)
    {

        $path = "ticket/detail/".$id;

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if(!empty($result['data'])) {
            return $result['data'];
        }else{
            return null;
        }
    }

    /**
     * 取得所有訂單列表
     * @return array
     */
    public function customize()
    {

        $path = "ticket/help";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        return $result['data'];
    }

    /**
     * 取得所有訂單列表
     * @return array
     */
    public function banner()
    {

        $result = [];
        try {
            $response = $this->request('GET', 'cart/detail');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }




    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return OrderResult
     */
    public function category($itemId)
    {

        $result = [];
        try {
            $response = $this->request('GET', 'cart/detail');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }

    /**
     * 根據訂單id 取得訂單細項資訊
     * @param $itemId
     * @return OrderResult
     */
    public function menu($itemId)
    {

        $result = [];
        try {
            $response = $this->request('GET', 'cart/detail');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        $order = new LayoutResult();
        $order->cityPass($result['data']);

        return $order;
    }
}