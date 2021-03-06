<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:28
 */

namespace Ksd\Mediation\Magento;

use Ksd\Mediation\Helper\EnvHelper;
use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Repositories\ProductRepository;
use Ksd\Mediation\Result\CartResult;
use Ksd\Mediation\Result\ProductResult;
use Ksd\Mediation\Magento\SalesRule;

use Ksd\Mediation\Cache\Redis;

class Cart extends Client
{
    use EnvHelper;

    private $productRepository;
    private $salesRule;


    public function __construct($defaultAuthorization = true)
    {
        parent::__construct($defaultAuthorization);
        $this->productRepository = new ProductRepository();
        $this->salesRule = new salesRule();
    }

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
     * @param int $memberId
     * @return CartResult
     */
    public function detail($get = false, $token = '')
    {
        $result = [];
        $totalResult = null;

        $cart = new CartResult();

        if ($get) {
            if ( ! empty($token)) {
                $this->userAuthorization($token);
            }

            try {

                $response = $this->request('GET', 'V1/carts/mine');
                $result = json_decode($response->getBody(), true);
                $totalResult = $this->totals();

            } catch (ClientException $e) {
                // TODO:處理抓取不到購物車資料
            }

            // $coupon = new SalesRule();
            /*if(!empty($totalResult['coupon_code'])) {
                $coupon = $this->salesRule->authorization($this->env('MAGENTO_ADMIN_TOKEN'))->salesRuleFindByCode($totalResult['coupon_code']);
                $cart->magento($result, $totalResult, $coupon);
            }*/
        }

        $cart->magento($result, $totalResult, null, true);
        $cart = $this->processItem($cart);

        return $cart;
    }

    /**
     * 取得購物車資訊
     * @return CartResult
     */
    public function mine()
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
        $cart->magento($result, $totalResult, null, false);
        $cart = $this->processItem($cart);

        return $cart;
    }

    /**
     * 建立空的購物車
     * @return string
     */
    public function createEmpty()
    {
        $response = $this->request('post', 'V1/carts/mine');
        return trim($response->getBody(), '"');
    }

    /**
     * 更新購物車金額
     * @param $id
     * @return bool
     */
    public function updateCart($id)
    {
        $parameter = [
            "cart_id" => $id,
                "address"=>[
                    "country_id" => "TW",
                ]
        ];
        $this->putParameters($parameter)->request('post', 'V1/carts/mine/billing-address');

        return true;
    }

    /**
     * 增加商品至購物車
     * @param $parameters
     * @return bool
     */
    public function add($parameters)
    {
        $isAdd = false;

        $cart = $this->mine();
        // 先移除全部
        $this->deleteAll($cart->id, $cart->items);

        $data = ['quote' => [
            'items' => []
        ]];

        if (!empty($cart->id)) {
            $data['quote']['id'] = $cart->id;
        } else {
            $data['quote']['id'] = $this->createEmpty();
        }
        foreach ($parameters as $item) {
            $row = new \stdClass();
            $row->sku = $this->parameterItemId($item);
            $row->qty = $item['quantity'];
            array_push($data['quote']['items'],$row);
        }

        $this->putParameters($data);

        try{
            $this->request('PUT', 'V1/carts/mine');
            $isAdd = true;
        } catch (ClientException $e) {
            // TODO:加入購物車失敗
            $isAdd = false;
        }

        return $isAdd;
    }

    /**
     * 增加商品至購物車
     * @param $parameters
     * @return bool
     */
    public function addCacheCart($memberId, $parameters)
    {
        $redis = new Redis();
        $key = sprintf('magentoCart.%s', $memberId);
        $redis->set($key, $parameters);

        return true;
    }


    /**
     * 取得商品至購物車
     * @param $parameters
     * @return bool
     */
    public function getOneOffCart($memberId)
    {
        $redis = new Redis;
        $key = sprintf('magentoCart.%s', $memberId);
        $val = $redis->pull($key);

        if ($val) $this->add($val);

        return $this->detail(true);
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     * @return bool
     */
    public function update($parameters)
    {
        $isUpdate = false;

        $cart = $this->detail();
        $items = $cart->items;
        foreach ($parameters as $parameter) {
            $index = $this->filterById($items, $this->parameterItemId($parameter));
            if (!is_null($index)) {
                $row = new ProductResult();
                $row->id = $this->parameterItemId($parameter);
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

            try{
                $this->request('POST', 'V1/carts/mine/items');
                $isUpdate = true;
            }
            catch (ClientException $e) {
                // TODO:更新購物車失敗
                $isUpdate = false;
                break;
            }
        }

        return $isUpdate;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function delete($parameters)
    {
        $cart = $this->detail();
        $items = $cart->items;
        $result=false;
        foreach ($parameters as $parameter) {
            $index = $this->filterById($cart->items, $this->parameterItemId($parameter));
            if (!is_null($index)) {
                $item = $items[$index];
                $path = sprintf('V1/carts/mine/items/%s', $item->itemId);
                $this->putParameter('cartId', $cart->id);
                $response = $this->request('delete', $path);
                $result = $response->getStatusCode() === 200;
            }
        }

        return $result;
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     * @return bool
     */
    public function deleteAll($cartId, $items = [])
    {
        if (!$items) return true;

        $result = false;
        foreach ($items as $item) {
            try {
                $path = sprintf('V1/carts/mine/items/%s', $item->itemId);
                $this->putParameter('cartId', $cartId);
                $response = $this->request('delete', $path);
                $result = $response->getStatusCode() === 200;
            } catch (ClientException $e) {
            }
        }

        return $result;
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

    /**
     * 處理規格商品 id 轉換
     * @param $item
     * @return mixed
     */
    private function parameterItemId($item)
    {
        if(array_key_exists('additionals', $item) && array_key_exists('priceId', $item['additionals'])) {
            return $item['additionals']['priceId'];
        }
        return $item['id'];
    }

    /**
     * 處理購物車品項
     * @param $cart
     * @return mixed
     */
    private function processItem($cart)
    {
        foreach ($cart->items as $index => $item) {
            $row = $this->productRepository->findFromIndex(ProjectConfig::MAGENTO, $item->id);
            if (!empty($row)) {
                $cart->items[$index]->spec = $row->specificationsText();
            }
        }
        return $cart;
    }
}
