<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Product;
use App\Models\Ticket\ProductWishlist;
use App\Models\Ticket\ProductTag;
use App\Models\Ticket\ProductSpec;
use App\Repositories\Ticket\ProductAdditionalRepository;
use App\Repositories\Ticket\ProductGroupRepository;

class ProductRepository extends BaseRepository
{
    protected $productAdditionalRepository;
    protected $productGroupRepository;

    public function __construct(Product $model, ProductAdditionalRepository $productAdditionalRepository, ProductGroupRepository $productGroupRepository)
    {
        $this->model = $model;
        $this->productAdditionalRepository = $productAdditionalRepository;
        $this->productGroupRepository = $productGroupRepository;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $id
     * @param $onShelf
     * @param $memberId
     * @return mixed
     */
    public function find($id, $onShelf = false, $memberId = 0)
    {
        $prod = $this->model->with(['imgs'])
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->find($id);

        if (!$prod) return null;

        $prod->imgs = $prod->imgs->sortBy(function($img) {
                        return $img->img_sort;
                    });

        $isMainProd = in_array($prod->prod_type, [1, 2]);

        $prod->spec = $this->productSpec($id);

        $prod->tags = ($isMainProd) ? $this->productTags($id) : null;

        $prod->isWishlist = ($isMainProd) ? $this->isWishlist($id, $memberId) : false;

        $prod->combos = ($isMainProd) ? $this->productGroup($id) : null;

        $prod->purchase = ($isMainProd) ? $this->productAdditional($id) : null;

        return $prod;
    }

    /**
     * 判斷是否為我的最愛
     * @param $id
     * @param $memberId
     * @return boolean
     */
    public function isWishlist($id, $memberId)
    {
        if (!$memberId) return false;

        return !ProductWishlist::where(['prod_id' => $id, 'member_id' => $memberId])->get()->isEmpty();
    }

    /**
     * 取得產品所有標籤
     * @param $id
     * @return boolean
     */
    public function productTags($id)
    {
        return ProductTag::with('tag')->where('prod_id', $id)->get();
    }

    /**
     * 取得產品所有規格
     * @param $id
     * @return boolean
     */
    public function productSpec($id)
    {
        return ProductSpec::with('specPrices')->where('prod_id', $id)->get();
    }

    /**
     * 取得產品底下所有加購商品
     * @param $id
     * @return boolean
     */
    public function productAdditional($id)
    {
        return $this->productAdditionalRepository->getAllByProdId($id);
    }

    /**
     * 取得產品底下所有組合商品
     * @param $id
     * @return boolean
     */
    public function productGroup($id)
    {
        return $this->productGroupRepository->getAllByProdId($id);
    }
}
