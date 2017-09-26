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
        $totalResult = null;
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



}