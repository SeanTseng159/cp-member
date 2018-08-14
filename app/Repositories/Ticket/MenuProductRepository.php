<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\MenuProd;
use App\Repositories\Ticket\ProductRepository;
use App\Repositories\MagentoProductRepository;

class MenuProductRepository extends BaseRepository
{
    protected $productRepository;
    protected $MagentoProductRepository;

    public function __construct(MenuProd $model, ProductRepository $productRepository, MagentoProductRepository $MagentoProductRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
    }

    /**
     * 取父分類產品
     * @param $tags
     * @return mixed
     */
    public function allByTagUpperId($lang, $id)
    {
        $data = $this->model->notDeleted()
                            ->where('tag_upper_id', $id)
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($data) {
            $data->transform(function ($row, $key) {
                if ($row->source === 1) {
                    $prod = $this->productRepository->easyFind($row->prod_id, true);
                }
                elseif ($row->source === 2) {
                    $prod = $this->MagentoProductRepository->find($row->prod_id);
                }

                if ($prod) {
                    // 取相關標籤
                    $prod->categories = $this->productCategories($row->prod_id);
                    $prod->tags = $this->productTags($row->prod_id);
                }

                $row->product = $prod;

                return $row;
            });

            $data = $data->reject(function ($row) {
                return (!$row->product);
            });
        }

        return $data;
    }

    /**
     * 取分類產品
     * @param $tags
     * @return mixed
     */
    public function allByTagId($lang, $id)
    {
        $data = $this->model->notDeleted()
                            ->where('tag_id', $id)
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($data) {
            $data->transform(function ($row, $key) {
                if ($row->source === 1) {
                    $prod = $this->productRepository->easyFind($row->prod_id, true);
                }
                elseif ($row->source === 2) {
                    $prod = $this->MagentoProductRepository->find($row->prod_id);
                }

                if ($prod) {
                    // 取相關標籤
                    $prod->categories = $this->productCategories($row->prod_id);
                    $prod->tags = $this->productTags($row->prod_id);
                }

                $row->product = $prod;

                return $row;
            });

            $data = $data->reject(function ($row) {
                return (!$row->product);
            });
        }

        return $data;
    }

    /**
     * 取得產品所有父標籤
     * @param $id
     * @return boolean
     */
    public function productCategories($id)
    {
        return $this->model->with('upperTag')->where('prod_id', $id)->get();
    }

    /**
     * 取得產品所有標籤
     * @param $id
     * @return boolean
     */
    public function productTags($id)
    {
        return $this->model->with('tag')->where('prod_id', $id)->get();
    }
}
