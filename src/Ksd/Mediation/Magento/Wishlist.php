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

class Wishlist extends BaseClient
{

    public function items()
    {

        $response = $this->request('GET', 'V1/ipwishlist/items');
        $result = json_decode($response->getBody(), true);

        $data=[];
        foreach ($result as $item) {
            $wish = new WishlistResult();
            $wish->magento($item);
            $data[] = $wish;
        }

        return $data;
    }

    public function add($sku)
    {

        $product = new Product();
        $productId = $product->product($sku)->productId;
        $url = sprintf('V1/ipwishlist/add/%s', $productId);
        $response = $this->request('POST', $url);
        $body = $response->getBody();
        $result = json_decode($body, true);
        return $result;
    }

    public function delete($wishlistItemId)
    {

        $url = sprintf('V1/ipwishlist/delete/%s', $wishlistItemId);
        $response = $this->request('DELETE', $url);
        $body = $response->getBody();
        $result = json_decode($body, true);
        return $result;
    }



}