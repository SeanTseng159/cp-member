<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:28
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Result\CartResult;
use Ksd\Mediation\Result\ProductResult;

class Cart extends BaseClient
{
    public function info()
    {
        $cart = $this->detail();
        return [
            'itemTotal' => $cart->itemTotal,
            'totalAmount' => $cart->totalAmount
        ];
    }

    public function detail()
    {
        $response = $this->request('GET', 'V1/carts/mine');

        $result = json_decode($response->getBody(), true);
        $cart = new CartResult();
        $cart->magento($result);
        return $cart;
    }

    public function add($parameters)
    {
        $cart = $this->detail();
        $data = ['quote' => [
            'id' => $cart->id,
            'items' => []
        ]];
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

    public function filterById($items, $id)
    {
        foreach ($items as $key => $item) {
            if ($item->id == $id) {
                return $key;
            }
        }

        return null;
    }
}