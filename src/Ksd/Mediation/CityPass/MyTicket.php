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
     * 票券物理主分類(目錄)
     * @param $hash
     * @return array
     */
    public function catalogIcon($hash)
    {

        $path = "ticket/catalogIcon";
        $response = $this->putQuery('hash',$hash)->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if(!empty($result['data'])) {
            return $result['data'];
        }else{
            return null;
        }

    }

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
     * 取得所有票券列表
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
     * 利用id取得細項資料
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
     * 利用id取得使用紀錄
     * @param $id
     * @return array
     */
    public function record($id)
    {

        $path = "ticket/record/".$id;

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
     * 轉贈票券
     *  @param $parameters
     * @param $id
     * @return bool
     */
    public function gift($parameters,$id)
    {
        $data = [
                    'serialNumber' => $parameters->serialNumber,
                    'memberId' => $id
                ];

        $response = $this->putParameters($data)->request('POST', 'ticket/gift');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 202) ? true : false;
    }

    /**
     * 轉贈退回
     *  @param $parameters
     * @return bool
     */
    public function refund($parameters)
    {

        $response = $this->putParameters($parameters)->request('POST', 'ticket/refund');
        $result = json_decode($response->getBody(), true);

        return ($result['statusCode'] === 202) ? true : false;
    }


}