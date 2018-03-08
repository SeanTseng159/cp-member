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
use Ksd\Mediation\Result\ProductResult;

class MagentoProductRepository
{
    protected $model;
    protected $result;

    public function __construct(MagentoProduct $model)
    {
        $this->model = $model;
        $this->result = new ProductResult;
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
                'type' => (isset($data->category) && isset($data->category['id'])) ? $data->category['id'] : '',
                'visibility' => (isset($data->visibility)) ? $data->visibility : 1,
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
        $type = $parameter->type;
        $visibility = $parameter->visibility;
        $page = $parameter->page ?: 1;
        $limit = $parameter->limit ?: 9999999;

        $page = ($page - 1) * $limit;

        $products = MagentoProduct::when($type, function ($query) use ($type) {
                            return $query->where('type', $type);
                        })
                        ->when($visibility, function ($query) use ($visibility) {
                            if ($visibility === '1') {
                                return $query->whereNotIn('visibility', [0, 1]);
                            }
                            elseif ($visibility === '0') {
                                return $query->whereIn('visibility', [0, 1]);
                            }
                        })
                        ->offset($page)
                        ->limit($limit)
                        ->get();

        if ($products) {
            foreach ($products as $p) {
                $data[$p->sku] = $this->result->magentoFormat(json_decode($p->data));
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

        return ($product) ? $this->result->magentoFormat(json_decode($product->data)) : null;
    }

    /**
     * 根據 所有id 取得對應商品明細
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function query($parameter)
    {
        if (!is_array($parameter->products)) return null;

        $data = null;

        foreach ($parameter->products as $p) {
            $product = $this->find($p);
            if ($product) $data[$product->id] = $product;
        }

        return $data;
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
