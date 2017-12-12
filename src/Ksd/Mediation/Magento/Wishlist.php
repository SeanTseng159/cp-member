<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/14
 * Time: 下午 04:45
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Result\WishlistResult;
use Ksd\Mediation\Magento\Product;
use Ksd\Mediation\Repositories\BaseRepository;

class Wishlist extends Client
{
    /**
     * 取得所有收藏列表
     * @return array
     */
    public function items()
    {
        if (empty($this->userToken)) {
            return [];
        }

        $response = $this->request('GET', 'V1/ipwishlist/items');
        $result = json_decode($response->getBody(), true);

        $data=[];
        foreach ($result as $item) {
            $wish = new WishlistResult();
            $wish->magento($item);
            $data[] = $wish;
        }
//        dd($data);
        return $data;
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param $sku
     * @return  bool
     */
    public function add($sku)
    {
        $product = new Product();
        $productId = $product->find($sku)->productId;
        $result = [];
        try {
            $url = sprintf('V1/ipwishlist/add/%s', $productId);
            $response = $this->request('POST', $url);
            $result = json_decode($response->getBody(), true);
        }catch (ClientException $e) {

        }
        return $result == 'true' ? true : false ;
    }

    /**
     * 根據商品id 刪除收藏清單商品
     * @param $wishlistItemId
     *  @return  bool
     */
    public function delete($wishlistItemId)
    {
        $result = [];
        try {
            $url = sprintf('V1/ipwishlist/delete/%s', $wishlistItemId);
            $response = $this->request('DELETE', $url);
            $result = json_decode($response->getBody(), true);
        }catch (ClientException $e) {

        }
        return $result == 'true' ? true : false ;
    }



}