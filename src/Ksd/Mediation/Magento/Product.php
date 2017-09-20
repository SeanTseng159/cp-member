<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 9:28
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Result\ProductCategoryResult;
use Ksd\Mediation\Result\ProductResult;

class Product extends Client
{
    use EnvHelper;

    /**
     * 根據 id 取得所有商品分類
     * @param int $id
     * @return ProductCategoryResult
     */
    public function categories($id = 1)
    {
        $path = "V1/categories/$id";
        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $category = new ProductCategoryResult();
        $category->magento($result);
        if (!empty($result['children'])) {
            $category->children = [];
            $children = mb_split(',', $result['children']);
            foreach ($children as $child) {
                $category->children[] = $this->categories($child);
            }
        }
        return $category;
    }

    /**
     * 根據 分類 id 取得對應商品列表
     * @param null $id
     * @return array
     */
    public function products($id = null)
    {
        $path = 'V1/products';
        if (!empty($id)) {
            $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'category_id')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $id);
        }
        $response = $this->putQuery('searchCriteria[pageSize]', $this->env('API_DATA_LIMIT', 500))
            ->request('GET', $path);
        $body = $response->getBody();
        $data = [];
        $result = json_decode($body, true);
        foreach ($result['items'] as $item) {
            $product = new ProductResult();
            $product->magento($item);
            $data[] = $product;
        }
        return $data;
    }

    /**
     * 根據 商品編號 取得商品明細
     * @param $sku
     * @return ProductResult
     */
    public function product($sku)
    {
        $path = "V1/products/$sku";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $product = new ProductResult();
        $product->magento($result, true);
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

        $path = 'V1/products';

        if (!empty($key)) {
            $response =$this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'name')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', '%' . $keyword . '%')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][condition_type]', 'like')
                ->putQuery('searchCriteria[filterGroups][0][filters][1][field]', 'description')
                ->putQuery('searchCriteria[filterGroups][0][filters][1][value]', '%' . $keyword . '%')
                ->putQuery('searchCriteria[filterGroups][0][filters][1][condition_type]', 'like')
                ->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);
            $data = [];
            foreach ($result['items'] as $item) {
                $product = new ProductResult();
                $product->magento($item);
                $data[] = $product;
            }

            return $data;
        }
    }
}