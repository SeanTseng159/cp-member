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

use Ksd\Mediation\Result\CartMoreResult as CartResult;
use Log;

class CartMore extends Client
{
    use EnvHelper;
    
    /**
     * 取得購物車簡易資訊
     * @return array
     */
    public function info($parameters)
    {
        $path = 'cartsAddMoreCarts/info';
        $result = [];
        try {
            
            $response = $this->setJson(false)->putParameters($parameters)
                ->request('POST', 'cartsAddMoreCarts/detail');
            $result = json_decode($response->getBody(), true);
            
        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        return [
            'itemTotal' => $result['data']['itemTotal'],
            'totalAmount' => $result['data']['totalAmount']
        ];
    }

    /**
     * 取得購物車資訊
     * @return CartResult
     */
    public function detail($parameters)
    {
        $result = [];
        try {
            
            $response = $this->setJson(false)->putParameters($parameters)
                ->request('POST', 'cartsAddMoreCarts/detail');
            $result = json_decode($response->getBody(), true);
            
        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }
        
        foreach($result['data'] as $item){
            $cart = new CartResult();
            $cart->cityPass($item);
            $data[]=$cart;
        }
        

        return $data;
    }

    /**
     * 增加商品至購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        
        $response = $this->setJson(false)->putParameters($parameters)
            ->request('POST', 'cartsAddMoreCarts/add');
        $result = json_decode($response->getBody(), true);
        
        //Log::debug('===購物車===');
        //Log::debug(print_r(json_decode($response->getBody(), true), true));

        //return ($result['statusCode'] === 201);
        return $result;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        $response = $this->setJson(false)->putParameters($parameters)
            ->request('POST', 'cartsAddMoreCarts/update');
        $result = json_decode($response->getBody(), true);

        //Log::debug('===購物車===');
        //Log::debug(print_r(json_decode($response->getBody(), true), true));

        //return ($result['statusCode'] === 202);
        return $result;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        $response = $this->setJson(false)->putParameters($parameters)
            ->request('POST', 'cartsAddMoreCarts/remove');
        $result = json_decode($response->getBody(), true);

        Log::debug('===購物車===');
        Log::debug(print_r(json_decode($response->getBody(), true), true));

        return $result;
    }
}
