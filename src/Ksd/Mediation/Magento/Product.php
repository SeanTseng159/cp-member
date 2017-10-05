<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 上午 9:28
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Result\ProductCategoryResult;
use Ksd\Mediation\Result\ProductResult;

class Product extends Client
{
    use EnvHelper;
    use ObjectHelper;

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
     * 根據 分類 id 取得所有商品列表
     * @param null $id
     * @return array
     */
    public function all($id = null)
    {
        $data = [];
        $total = 1;
        $limit = $this->env('API_DATA_LIMIT', 100);
        $page = 1;
        while (count($data) < $total) {
            $result = $this->category($id, $limit, $page);
            $total = $result['total_count'];
            foreach ($result['items'] as $item) {
                $product = new ProductResult();
                $product->magento($item);
                $data[] = $product;
            }
            $page++;
        }
        return $data;
    }

    /**
     * 根據 分類 id 取得對應商品列表
     * @param null $id
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function category($id = null, $limit = 100, $page = 1)
    {
        $path = 'V1/products';
        if (!empty($id)) {
            $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'category_id')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $id);
        }
        $response = $this->putQuery('searchCriteria[pageSize]', $limit)
            ->putQuery('searchCriteria[currentPage]	', $page)
            ->request('GET', $path);
        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * 根據 商品編號 取得商品明細
     * @param $sku
     * @return ProductResult
     */
    public function find($sku)
    {
        $path = "V1/products/$sku";

        $response = $this->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);
        $product = new ProductResult();
        $product->magento($result, true);
        $product->additionals = $this->additional($result);
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

    /**
     * 根據 id 找出對應商品
     * @param $id
     * @return ProductResult|null
     */
    public function findById($id)
    {
        $path = 'V1/products';

        $response =$this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'entity_id')
            ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', $id)
            ->request('GET', $path);
        $body = $response->getBody();
        $result = json_decode($body, true);

        if(count($result['items']) > 0) {
            return $this->find($result['items'][0]['sku']);
        }

        return null;

    }

    /**
     * 根據 商品 attributes id 取得商品 attributes
     * @param $id
     * @return mixed
     */
    public function attributes($id)
    {
        $url = sprintf('V1/products/attributes/%s', $id);
        $response = $this->request('GET', $url);
        return json_decode($response->getBody(), true);
    }

    /**
     * 取得所有符合規格商品資訊
     * @param $result
     * @return array
     */
    private function additional($result)
    {
        $extensionAttributes = $result['extension_attributes'];
        $specs = [];
        if (array_key_exists('configurable_product_options', $extensionAttributes)) {
            $productOptions = $result['extension_attributes']['configurable_product_options'];

            $attributeCodes = [];
            $filters = [];
            foreach ($productOptions as $key => $productOption) {
                $attributes = $this->attributes($productOption['attribute_id']);
                $spec = [
                    'label' => $productOption['label'],
                    'code' => $attributes['attribute_code']
                ];

                $options = $attributes['options'];
                foreach ($productOption['values'] as $index => $value) {
                    $spec['spec'][] = [
                        'value' => $this->optionLabel($options, $value['value_index']),
                        'value_index' => $value['value_index']
                    ];
                }
                $specs =  $this->addSpec($specs, $spec);
                $attributeCodes[] = $attributes['attribute_code'];
                $filters[]['code'] = $attributes['attribute_code'];
            }


            $productLinks = $result['extension_attributes']['configurable_product_links'];
            foreach ($productLinks as $productLink) {
                $product = $this->findById($productLink);
                foreach ($filters as $key => $filter) {
                    $filters[$key]['value'] = $this->customAttributes($product->customAttributes, $filter['code']);
                }
                $specs['spec'] = $this->putQuantity($product, $filters, $specs['spec']);
            }
        }
        return array_key_exists('spec', $specs) ? $specs['spec'][0] : $specs;
    }

    /**
     * 處理商品規格
     * @param $result
     * @param $specs
     * @return mixed
     */
    private function addSpec($result, $specs)
    {
        if (array_key_exists('spec', $result) && count($result['spec']) > 0) {
            foreach ($result['spec'] as $key => $row) {
                $result['spec'][$key] = $this->addSpec($row, $specs);
            }
        } else {
            $result['spec'][] = $specs;
        }
        return $result;
    }

    /**
     * 處理商品規格 label
     * @param $options
     * @param $value
     * @return string
     */
    private function optionLabel($options, $value)
    {
        foreach ($options as $option) {
            if($option['value'] == $value) {
                return $option['label'];
            }
        }
        return '';
    }

    /**
     * 取得所有規格商品數量
     * @param $product
     * @param $filters
     * @param $result
     * @return mixed
     */
    private function putQuantity($product, $filters, $result)
    {
        while (!empty($filters)) {
            $filter = array_shift($filters);
            foreach ($result as $key => $row) {
                if ($filter['code'] == $row['code']) {
                    foreach ($row['spec'] as $index => $spec) {
                        if(array_key_exists('value_index', $spec) && $filter['value'] == $spec['value_index']) {
                            if(array_key_exists('spec', $spec)) {
                                $spec['spec'] = $this->putQuantity($product, $filters, $spec['spec']);
                                $result[$key]['spec'][$index] = $spec;
                            } else {
                                $result[$key]['spec'][$index]['id'] = $product->id;
                                $result[$key]['spec'][$index]['quantity'] = $product->quantity;
                            }
                            break;
                        }
                    }
                }
            }

        }
        return $result;
    }
}