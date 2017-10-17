<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/17
 * Time: 上午 11:32
 */

namespace Ksd\Mediation\Result\Product;


class ProductIndexResult
{
    /**
     * 處理 magento 產品索引檔
     * @param $product
     */
    public function magneto($product)
    {
        $this->id = $product->id;
        $this->productId = $product->productId;
        $this->quantity = $product->quantity;
        $this->price = $product->price;
        $this->salePrice = $product->salePrice;
        $this->configurableProductOptions = [];

        if (property_exists($product, 'specifications')) {
            $this->specifications = $product->specifications;
        }

        if (empty($this->configurableProductOptions)) {

            $this->type = 'configurable';
        } else {
            $this->type = 'simple';
        }

        $this->configurableProducts = [];
        if (!empty($product->configurableProducts)) {
            foreach ($product->configurableProducts as $configurableProduct) {
                $productIndex = new ProductIndexResult();
                $productIndex->magneto($configurableProduct);
                $this->configurableProducts[] = $productIndex;
            }
            $this->configurableProductOptions = $product->configurableProductOptions;
        }

    }

    /**
     * 根據 id 找出產品
     * @param $id
     * @return $this|null
     */
    public function find($id)
    {
        if ($this->id == $id) {
            return $this;
        }
        if (!empty($this->configurableProducts)) {
            foreach ($this->configurableProducts as $configurableProduct) {
                if ($configurableProduct->id == $id) {
                    return $configurableProduct;
                }
            }
        }
        return null;
    }
}