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
use App\Models\Ticket\ProductKeyword;
use App\Models\Ticket\ProductSpec;
use App\Models\Ticket\MenuProd;
use App\Repositories\Ticket\ProductAdditionalRepository;
use App\Repositories\Ticket\ProductGroupRepository;
use App\Repositories\Ticket\MenuProductRepository;
use App\Repositories\Ticket\TagProdRepository;
use Illuminate\Pagination\Paginator;
use App\Config\Ticket\ProcuctConfig;
use Carbon\Carbon;

class ProductRepository extends BaseRepository
{
    protected $date;
    protected $productAdditionalRepository;
    protected $productGroupRepository;
    protected $menuProductRepository;
    protected $tagProdRepository;

    public function __construct(Product $model, ProductAdditionalRepository $productAdditionalRepository, ProductGroupRepository $productGroupRepository, TagProdRepository $tagProdRepository)
    {
        $this->date = Carbon::now()->toDateTimeString();

        $this->model = $model;
        $this->productAdditionalRepository = $productAdditionalRepository;
        $this->productGroupRepository = $productGroupRepository;
        $this->tagProdRepository = $tagProdRepository;
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
        $prod = $this->model->with(['imgs' => function($query) {
                                return $query->orderBy('img_sort')->get();
                            }, 'specs.specPrices'])
                            ->notDeleted()
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            // ->where('prod_type', '!=', 4)
                            //->where('prod_onshelf_time', '<=', $this->date)
                            //->where('prod_offshelf_time', '>=', $this->date)
                            ->find($id);
        if (!$prod) return null;

        // 組合子商品
        if ($prod->prod_type == 4) return $prod;

        // 檢查上下架時間
        if ($this->date < $prod->prod_onshelf_time || $this->date > $prod->prod_offshelf_time) return null;

        $isMainProd = in_array($prod->prod_type, [1, 2]);

        if ($isMainProd) {
            $this->menuProductRepository = app()->build(MenuProductRepository::class);
            $prod->categories = $this->menuProductRepository->tags($id);
        }
        else {
            $prod->categories = [];
        }

        $prod->keywords = ($isMainProd) ? $this->productKeywords($id) : null;

        $prod->isWishlist = ($isMainProd && $memberId) ? $this->isWishlist($id, $memberId) : false;

        $prod->combos = ($isMainProd) ? $this->productGroup($id) : null;

        $prod->purchase = ($isMainProd) ? $this->productAdditional($id) : null;

        return $prod;
    }

    /**
     * 根據 商品 id 取得所有加購商品明細
     * @param $id
     * @param $onShelf
     * @return mixed
     */
    public function findPurchase($id, $onShelf = false)
    {
        $prod = $this->model->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->notDeleted()
                            ->where('prod_type', '!=', 4)
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->find($id);

        if (!$prod) return null;

        $isMainProd = in_array($prod->prod_type, [1, 2]);

        $prod->purchase = ($isMainProd) ? $this->productAdditional($id) : null;

        return $prod;
    }

    /**
     * 根據 組合商品(內容物) id 取得商品明細
     * @param $id
     * @param $onShelf
     * @return mixed
     */
    public function findComboItem($id, $onShelf = false)
    {
        $prod = $this->model->with(['imgs' => function($query) {
                                return $query->orderBy('img_sort')->get();
                            }])
                            ->notDeleted()
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->where('prod_type', 4)
                            ->find($id);

        if (!$prod) return null;

        return $prod;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $id
     * @param $onShelf
     * @return mixed
     */
    public function easyFind($id, $onShelf = false)
    {
        $prod = $this->model->with(['specs.specPrices', 'imgs' => function($query) {
                                return $query->orderBy('img_sort')->first();
                            }])
                            ->notDeleted()
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->find($id);

        return $prod;
    }

    /**
     * 根據 商品 id 取得商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function findByCheckout($id, $specId, $specPriceId, $hasTag = false)
    {
        $prod = $this->model->with(['shippingFees', 'img', 'groups'])->leftJoin('prod_specs', 'prods.prod_id', '=', 'prod_specs.prod_id')
                            ->leftJoin('prod_spec_prices', 'prod_specs.prod_spec_id', '=', 'prod_spec_prices.prod_spec_id')
                            ->where('prod_specs.prod_spec_id', $specId)
                            ->where('prod_spec_prices.prod_spec_price_id', $specPriceId)
                            ->where('prods.deleted_at', 0)
                            ->whereIn('prod_type', [1, 2])
                            ->where('prods.prod_onshelf', 1)
                            ->where('prods.prod_onshelf_time', '<=', $this->date)
                            ->where('prods.prod_offshelf_time', '>=', $this->date)
                            ->where('prods.prod_onsale_time', '<=', $this->date)
                            ->where('prods.prod_offsale_time', '>=', $this->date)
                            ->find($id);

        if (!$prod) return null;

        if ($prod->prod_type === 2) {
            $newGroups = [];
            foreach ($prod->groups as $group) {
                $p = $this->findSubCobmoByCheckout($group->prod_group_prod_id, $group->prod_group_spec_id, $group->prod_group_price_id, $hasTag);
                $p->prod_spec_price_value = $group->prod_group_share;

                $newGroups[] = $p;
            }

            $prod->groups = $newGroups;
        }

        if ($hasTag) {
            $prod->tags = $this->tagProdRepository->getTagsByProdId($id);
        }

        return $prod;
    }

    /**
     * 根據 商品 id 取得商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function findAdditionalByCheckout($id, $specId, $specPriceId, $hasTag = false)
    {
        $prod = $this->model->with(['shippingFees', 'img'])->leftJoin('prod_specs', 'prods.prod_id', '=', 'prod_specs.prod_id')
                            ->leftJoin('prod_spec_prices', 'prod_specs.prod_spec_id', '=', 'prod_spec_prices.prod_spec_id')
                            ->where('prod_specs.prod_spec_id', $specId)
                            ->where('prod_spec_prices.prod_spec_price_id', $specPriceId)
                            ->where('prods.deleted_at', 0)
                            ->where('prod_type', 3)
                            ->where('prods.prod_onshelf', 1)
                            ->where('prods.prod_onsale_time', '<=', $this->date)
                            ->where('prods.prod_offsale_time', '>=', $this->date)
                            ->find($id);

        if ($hasTag) {
            $prod->tags = $this->tagProdRepository->getTagsByProdId($id);
        }

        return $prod;
    }

    /**
     * 根據 商品 id 取得商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function findSubCobmoByCheckout($id, $specId, $specPriceId, $hasTag = false)
    {
        $prod = $this->model->leftJoin('prod_specs', 'prods.prod_id', '=', 'prod_specs.prod_id')
                            ->leftJoin('prod_spec_prices', 'prod_specs.prod_spec_id', '=', 'prod_spec_prices.prod_spec_id')
                            ->where('prod_specs.prod_spec_id', $specId)
                            ->where('prod_spec_prices.prod_spec_price_id', $specPriceId)
                            ->where('prods.deleted_at', 0)
                            ->where('prod_type', 4)
                            ->find($id);

        if ($hasTag) {
            $prod->tags = $this->tagProdRepository->getTagsByProdId($id);
        }

        return $prod;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $id
     * @return mixed
     */
    public function mainProductFind($id, $onShelf = false, $onSearch = false)
    {
        $prod = $this->model->with(['specs.specPrices', 'imgs' => function($query) {
                                return $query->orderBy('img_sort')->first();
                            }])
                            ->notDeleted()
                            ->when($onShelf, function($query) {
                                $query->where('prod_onshelf', 1);
                            })
                            ->when($onSearch, function($query) {
                                $query->where('on_search', 1);
                            })
                            ->whereIn('prod_type', [1, 2])
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->find($id);

        return $prod;
    }

    /**
     * 根據 商品 ids 取得所有商品明細
     * @param $id
     * @param $onShelf
     * @return mixed
     */
    public function allById($idArray = [], $onShelf = false)
    {
        $prods = $this->model->with(['specs.specPrices', 'img'])
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->notDeleted()
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->whereIn('prod_id', $idArray)
                            ->get();

        return $prods;
    }

    /**
     * 依 關鍵字 找商品
     * @param $keyword
     * @return mixed
     */
    public function search($keyword)
    {
        $data = $this->model->with(['specs.specPrices', 'imgs' => function($query) {
                                return $query->orderBy('img_sort')->first();
                            }])
                            ->notDeleted()
                            ->where('on_search', 1)
                            ->where('prod_onshelf', 1)
                            ->whereIn('prod_type', [1, 2])
                            ->where('prod_name', 'like', '%' . $keyword . '%')
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->get();

        return $data;
    }

    /**
     * 取得產品所有關鍵字
     * @param $id
     * @return mixed
     */
    public function isWishlist($id, $memberId)
    {
        if (!$memberId) return false;

        return !ProductWishlist::where(['prod_id' => $id, 'member_id' => $memberId])->get()->isEmpty();
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

    /**
     * 取得產品所有標籤
     * @param $id
     * @return mixed
     */
    public function productTags($id)
    {
        return ProductTag::with('tag')->where('prod_id', $id)->get();
    }

    /**
     * 取得產品所有規格
     * @param $id
     * @return mixed
     */
    public function productSpec($id)
    {
        return ProductSpec::with('specPrices')->where('prod_id', $id)->notDeleted()->get();
    }

    /**
     * 取得產品底下所有加購商品
     * @param $id
     * @return mixed
     */
    public function productAdditional($id)
    {
        return $this->productAdditionalRepository->getAllByProdId($id);
    }

    /**
     * 取得產品底下所有組合商品
     * @param $id
     * @return mixed
     */
    public function productGroup($id)
    {
        return $this->productGroupRepository->getAllByProdId($id);
    }

    public function productMainTag($id)
    {
        return ProductTag::with('tag')
            ->where('prod_id', $id)
            ->where('is_main', 1)
            ->get();
    }

    /**
     * 取得供應商商品
     * @params $suppliedId
     * @return Collections
     */
    public function supplierProducts(int $supplierId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        Paginator::currentPageResolver(function() use ($page) {
            return $page;
        });

        $data = $this->model->with(['specs.specPrices', 'img'])
                    ->where('supplier_id', $supplierId)
                    ->notDeleted()
                    ->where('prod_onshelf', 1)
                    ->whereIn('prod_type', [1, 2])
                    ->where('prod_onshelf_time', '<=', $this->date)
                    ->where('prod_offshelf_time', '>=', $this->date)
                    ->paginate($limit);

        return $data;
    }
}
