<?php
/**
 * User: lee
 * Date: 2018/03/04
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\MagentoProduct;
use App\Models\Ticket\ProductKeyword;
use Ksd\Mediation\Magento\Product as Magento;
use App\Repositories\Ticket\MenuProductRepository;

class MagentoProductRepository
{
    protected $model;
    protected $result;

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
                $data[$p->sku] = json_decode($p->data);
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

        if (!$product) return null;

        $product = json_decode($product->data);

        $this->menuProductRepository = app()->build(MenuProductRepository::class);
        $product->categories = $this->menuProductRepository->tags($id);

        $product->keywords = $this->productKeywords($id);

        return $product;
    }

    /**
     * 根據 商品 ids 取得所有商品明細
     * @param $idArray
     * @return mixed
     */
    public function allById($idArray = [])
    {
        $prods = $this->model->whereIn('sku', $idArray)->get();

        if ($prods) {
            $prods->transform(function ($item, $key) {
                $item->data = json_decode($item->data);

                return $item;
            });
        }

        return $prods;
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
     * 商品搜尋
     * @param $keyword
     * @return \App\Models\MagentoProduct
     */
    public function search($keyword)
    {
        $prods = $this->model->where('sku', 'like', '%' . $keyword . '%')->get();

        if ($prods) {
            $prods->transform(function ($item, $key) {
                $item->data = json_decode($item->data);

                return $item;
            });
        }

        return $prods;
    }

    /**
     * 同步magento所有商品資料
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

    /**
     * 取得產品所有關鍵字
     * @param $id
     * @return mixed
     */
    public function productKeywords($id)
    {
        return ProductKeyword::with('keyword')->where('prod_id', $id)->get();
    }
}
