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

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $id
     * @param $memberId
     * @return mixed
     */
    public function find($id, $memberId)
    {
        $prod = $this->model->with(['imgs'])->find($id);

        $prod->isWishlist = $this->isWishlist($id, $memberId);

        $prod->tags = $this->productTags($id);

        $prod->spec = $this->productSpec($id);

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
}
