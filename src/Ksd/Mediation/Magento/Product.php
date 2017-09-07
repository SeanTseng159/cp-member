<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 9:28
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Result\ProductCategoryResult;
use Ksd\Mediation\Result\ProductResult;

class Product extends BaseClient
{
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

    public function products($id = null)
    {
        $path = 'V1/products';
        if (!empty($id)) {
            $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'category_id')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $id);
        }
        $response = $this->putQuery('searchCriteria[pageSize]', 63353)
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
}