<?php
/**
 * User: lee
 * Date: 2018/03/04
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\MagentoProduct;
use Ksd\Mediation\Magento\Product as Magento;

class MagentoProductRepository
{
    protected $model;

    public function __construct(MagentoProduct $model)
    {
        $this->model = $model;
    }

    /**
     * 新增/更新 商品資料
     * @param $data
     * @return mixed
     */
    public function createOrUpdate($sku, $data)
    {
        try {
            return $this->model->updateOrCreate([
                'sku' => $sku
            ], [
                'sku' => $sku,
                'data' => json_encode($data)
            ]);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 取得所有商品資料
     * @param $data
     * @return mixed
     */
    public function all($parameter)
    {
        $page = $parameter->page ?: 1;
        $limit = $parameter->limit ?: 9999999;

        $page = ($page - 1) * $limit;

        $products = $this->model->offset($page)->limit($limit)->get();

        if ($products) {
            foreach ($products as $p) {
                $data[] = json_decode($p->data);
            }
            return $data;
        }

        return null;
    }

    /**
     * 取得單一商品資料
     * @param $data
     * @return mixed
     */
    public function find($id)
    {
        $product = $this->model->where('sku', $id)->first();

        return ($product) ? json_decode($product->data) : null;
    }

    /**
     * 根據 所有id 取得對應商品明細
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function query($parameter)
    {
        $list = explode(',', $parameter->products);
        if (!is_array($list)) return null;

        $products = $this->model->whereIn('sku', $list)->get();

        if ($products) {
            foreach ($products as $p) {
                $data[] = json_decode($p->data);
            }
            return $data;
        }

        return null;
    }

    /**
     * 同步magento所有商品資料
     * @param $data
     * @return mixed
     */
    public function syncAll()
    {
        $magento = new Magento();
        $magentoProducts = $magento->all();

        if ($magentoProducts) {
            foreach ($magentoProducts as $product) {
                $products[] = $magento->find($product->id);
            }

            return $products;
        }

        return null;
    }
}
