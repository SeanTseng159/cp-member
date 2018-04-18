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
    public function all($categories = null)
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
        if ($result['status']) {
            foreach ($result['data']['result'] as $item) {
                $product = new ProductResult();
                $product->cityPass($item);
                $data[] = $product;
            }
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
        $this->logger->info($body);
        $product = null;
        if ($result['status']) {
            $product = new ProductResult();
            $product->cityPass($result['data'], true);
        }
        return $product;
    }

    /**
     * 根據 id 取得加購商品
     * @param $id
     * @return ProductResult
     */
    public function purchase($id)
    {
        $path = "product/purchase/$id";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $this->logger->info($body);
        $product = null;
        if ($result['status']) {
            $product = $result['data'];
        }
        return $product;
    }

    /**
     * 根據 關鍵字 做模糊搜尋 取得商品列表
     * @param $key
     * @return array
     */
    public function search($key)
    {
        $keyword = $key->search;
        $path = "product/search";

        $response  = $this->putQuery('search',$keyword)->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        $data =[];
        foreach ($result['data'] as $item) {
            $product = new ProductResult();
            $product->cityPass($item);
            $data[] = $product;
        }

        return $data;

    }

    /**
     * 清除 layout 中 magento 商品
     * @param $id
     */
    public function updateMagentoProduct($id)
    {
        $path = "Prod/updateMagentoProd/$id";
        $response = $this->request('GET', $path);
        $body = $response->getBody();
        return json_encode($body, true);
    }
}
