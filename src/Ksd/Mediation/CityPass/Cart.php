<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/26
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\CityPass;
use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\CartResult;

class Cart extends Client
{
    use EnvHelper;

    /**
     * 取得購物車簡易資訊
     * @return array
     */
    public function info()
    {
        $path = 'cart/info';

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        return [
            'itemTotal' => $result['data']['itemTotal'],
            'totalAmount' => $result['data']['totalAmount']
        ];
    }

    /**
     * 取得購物車資訊
     * @return CartResult
     */
    public function detail()
    {
        $result = [];
        try {
            $response = $this->request('GET', 'cart/detail');
            $result = json_decode($response->getBody(), true);

        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        $cart = new CartResult();
        $cart->cityPass($result['data']);

        return $cart;
    }

    /**
     * 增加商品至購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        $response = $this->setJson(false)->putParameters($parameters)
            ->request('POST', 'cart/add');
        $result = json_decode($response->getBody(), true);
        if ($result['statusCode'] === 201) {
            return true;
        }
        return false;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'cart/update');
        $result = json_decode($response->getBody(), true);
        if ($result['statusCode'] === 202) {
            return true;
        }
        return false;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'cart/remove');
        $result = json_decode($response->getBody(), true);
        if ($result['statusCode'] === 203) {
            return true;
        }
        return false;
    }
}
