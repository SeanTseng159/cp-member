<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 9:28
 */

namespace Ksd\Mediation\CityPass;


use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\ProductResult;

class Product extends Client
{
    use EnvHelper;

    /**
     * 根據 商品分類 取得商品列表
     * @param null $categories
     * @return array
     */
    public function products($categories = null)
    {
        $path = 'product/all';

        if(!empty($categories)) {
            $this->putParameter('tags[]', $categories);
        }

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $data = [];
        $result = json_decode($body, true);
        foreach ($result['data']['result'] as $item) {
            $product = new ProductResult();
            $product->cityPass($item);
            $data[] = $product;
        }
        return $data;
    }

    /**
     * 根據 商品分類 取得商品列表
     * @param $categories
     * @return array
     */
    public function tags($categories)
    {
        $path = 'product/tags';

        if(!empty($categories)) {
            $this->putParameter('names[]', $categories);
        }

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $data = [];
        $result = json_decode($body, true);
        foreach ($result['data']['result'] as $item) {
            $product = new ProductResult();
            $product->cityPass($item);
            $data[] = $product;
        }
        return $data;


    }

    /**
     * 根據 id 取得商品明細
     * @param $id
     * @return ProductResult
     */
    public function find($id)
    {
        $path = "product/query/$id";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $product = new ProductResult();
        $product->cityPass($result['data'], true);
        return $product;
    }
}