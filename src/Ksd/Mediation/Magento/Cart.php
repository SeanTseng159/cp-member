<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:28
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Result\CartResult;
use Ksd\Mediation\Result\ProductResult;

class Cart extends Client
{
    /**
     * 取得購物車簡易資訊
     * @return array
     */
    public function info()
    {
        $cart = $this->detail();
        return [
            'itemTotal' => $cart->itemTotal,
            'totalAmount' => $cart->totalAmount
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
            $response = $this->request('GET', 'V1/carts/mine');
            $result = json_decode($response->getBody(), true);
            $totalResult = $this->totals();
        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }

        $cart = new CartResult();
        $cart->magento($result, $totalResult);

        return $cart;
    }

    /**
     * 增加商品至購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        $cart = $this->detail();
        $data = ['quote' => [
            'items' => []
        ]];
        if (!empty($cart->id)) {
            $data['quote']['id'] = $cart->id;
        }
        foreach ($parameters as $item) {
            $row = new \stdClass();
            $row->sku = $item['id'];
            $row->qty = $item['quantity'];
            array_push($data['quote']['items'],$row);
        }

        $this->putParameters($data);
        $this->request('PUT', 'V1/carts/mine');
        return true;
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        $cart = $this->detail();
        $items = $cart->items;

        foreach ($parameters as $parameter) {
            $index = $this->filterById($items, $parameter['id']);
            if (!is_null($index)) {
                $row = new ProductResult();
                $row->id = $parameter['id'];
                $row->qty = $parameter['quantity'];
                $items[$index] = $row;
            }

        }
        $data = new \stdClass();

        foreach ($items as $item) {
            $row = new \stdClass();
            $row->sku = $item->id;
            $row->qty = $item->qty;
            $row->quote_id = $cart->id;
            $data->cartItem = $row;

            $this->delete([
                ['id' => $item->id]
            ]);

            $this->putParameters($data);
            $this->request('POST', 'V1/carts/mine/items');
        }

        return true;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     */
    public function delete($parameters)
    {
        $cart = $this->detail();
        $items = $cart->items;
        foreach ($parameters as $parameter) {
            $index = $this->filterById($cart->items, $parameter['id']);
            if (!is_null($index)) {
                $item = $items[$index];
                $path = sprintf('V1/carts/mine/items/%s', $item->itemId);
                $this->putParameter('cartId', $cart->id);
                $this->request('delete', $path);
            }
        }
    }

    /**
     * 取得購物車內商品索引值
     * @param $items
     * @param $id
     * @return int|null|string
     */
    public function filterById($items, $id)
    {
        foreach ($items as $key => $item) {
            if ($item->id == $id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * 取得購物車金額
     * @return mixed
     */
    public function totals()
    {
        $path = 'V1/carts/mine/totals';
        $response = $this->request('GET', $path);
        return json_decode($response->getBody(), true);
    }
}