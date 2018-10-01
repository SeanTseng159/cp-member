<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Keyword;

use App\Repositories\Ticket\ProductRepository;
use App\Repositories\MagentoProductRepository;

class KeywordRepository extends BaseRepository
{
    protected $productRepository;
    protected $MagentoProductRepository;

    public function __construct(Keyword $model, ProductRepository $productRepository, MagentoProductRepository $MagentoProductRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
    }

    /**
     * 依 關鍵字 找商品
     * @return mixed
     */
    public function getProductsByKeyword($keyword)
    {
        $data = $this->model->with(['keywordProducts'])
                            ->notDeleted()
                            ->where('keyword_text', 'like', '%' . $keyword . '%')
                            ->get();

        if (!$data) return null;

        $data->transform(function ($row, $key) {
            $products = [];

            foreach ($row->keywordProducts as $product) {
                if ($product->source === 1) {
                    $prod = $this->productRepository->easyFind($product->prod_id, true);
                }
                elseif ($product->source === 2) {
                    $prod = $this->MagentoProductRepository->find($product->prod_id);
                }

                if ($prod) $products[] = $prod;
            }

            $row->items = $products;

            return $row;
        });

        return $data;
    }
}
