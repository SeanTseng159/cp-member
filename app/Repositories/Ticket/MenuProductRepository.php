<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\MenuProd;
use App\Models\Ticket\Tag;
use App\Repositories\Ticket\ProductRepository;
use App\Repositories\MagentoProductRepository;

class MenuProductRepository extends BaseRepository
{
    protected $productRepository;
    protected $MagentoProductRepository;

    public function __construct(MenuProd $model, ProductRepository $productRepository, MagentoProductRepository $MagentoProductRepository)
    {
        $this->missionModel = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
    }

    /**
     * 取父分類產品
     * @param $tags
     * @return mixed
     */
    public function productsByTagUpperId($lang, $id)
    {
        $data = $this->missionModel->select('source', 'prod_id')
                            ->notDeleted()
                            ->where('tag_upper_id', $id)
                            ->get()
                            ->unique('prod_id')
                            ->groupBy('source');

        $products = collect([]);

        if ($data) {

            $ticketProducts = $commodityProducts = [];

            foreach ($data as $source => $item) {
                if ($source === 1) $ticketProducts = $item->pluck('prod_id');
                elseif ($source === 2) $commodityProducts = $item->pluck('prod_id');
            }

            // 票卷
            if ($ticketProducts) {
                $ticketProducts = $this->productRepository->allById($ticketProducts, true);
                /*$ticketProducts->transform(function ($prod) {
                    $prod->categories = $this->productCategories($prod->prod_id);
                    $prod->tags = $this->productTags($prod->prod_id);

                    return $prod;
                });*/

                $products->push($ticketProducts);
            }
            // 實體
            if ($commodityProducts) {
                $commodityProducts = $this->MagentoProductRepository->allById($commodityProducts);
                /*$commodityProducts->transform(function ($prod) {
                    $prod->categories = $this->productCategories($prod->data->id);
                    $prod->tags = $this->productTags($prod->data->id);

                    return $prod;
                });*/

                $products->push($commodityProducts);
            }
        }

        return $products->flatten()->sortByDesc('created_at');
    }

    /**
     * 取分類產品
     * @param $tags
     * @return mixed
     */
    public function productsByTagId($lang, $id)
    {
        $data = $this->missionModel->select('source', 'prod_id')
                            ->notDeleted()
                            ->where('tag_id', $id)
                            ->get()
                            ->unique('prod_id')
                            ->groupBy('source');

        $products = collect([]);

        if ($data) {

            $ticketProducts = $commodityProducts = [];

            foreach ($data as $source => $item) {
                if ($source === 1) $ticketProducts = $item->pluck('prod_id');
                elseif ($source === 2) $commodityProducts = $item->pluck('prod_id');
            }

            // 票卷
            if ($ticketProducts) {
                $ticketProducts = $this->productRepository->allById($ticketProducts, true);
                /*$ticketProducts->transform(function ($prod) {
                    $prod->categories = $this->productCategories($prod->prod_id);
                    $prod->tags = $this->productTags($prod->prod_id);

                    return $prod;
                });*/

                $products->push($ticketProducts);
            }
            // 實體
            if ($commodityProducts) {
                $commodityProducts = $this->MagentoProductRepository->allById($commodityProducts);
                /*$commodityProducts->transform(function ($prod) {
                    $prod->categories = $this->productCategories($prod->data->id);
                    $prod->tags = $this->productTags($prod->data->id);

                    return $prod;
                });*/

                $products->push($commodityProducts);
            }
        }

        return $products->flatten()->sortByDesc('created_at');
    }

    /**
     * 取得產品所有父標籤
     * @param $id
     * @return boolean
     */
    public function productCategories($id)
    {
        return $this->missionModel->with('upperTag')->notDeleted()->where('prod_id', $id)->get();
    }

    /**
     * 取得產品所有標籤
     * @param $id
     * @return boolean
     */
    public function productTags($id)
    {
        return $this->missionModel->with('tag')->notDeleted()->where('prod_id', $id)->get();
    }

    /**
     * 取得產品所有標籤
     * @param $id
     * @return boolean
     */
    public function tags($id)
    {
        $menuProducts = $this->missionModel->notDeleted()->where('prod_id', $id)->get();

        if ($menuProducts->isEmpty()) return [];

        foreach ($menuProducts as $product) {
            $list[] = $product['tag_upper_id'];
            $list[] = $product['tag_id'];
        }

        $list = array_unique($list);

        return Tag::whereIn('tag_id', $list)->where('tag_status', 1)->orderBy('tag_sort', 'asc')->get();
    }
}
