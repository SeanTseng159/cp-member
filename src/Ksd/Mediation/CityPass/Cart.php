<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/26
 * Time: 下午 04:57
 */

namespace Ksd\Mediation\CityPass;

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





}