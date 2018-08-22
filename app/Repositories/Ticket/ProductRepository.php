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
use Illuminate\Pagination\Paginator;
use App\Config\Ticket\ProcuctConfig;

use Carbon\Carbon;

class ProductRepository extends BaseRepository
{
    protected $date;
    protected $productAdditionalRepository;
    protected $productGroupRepository;
    protected $menuProductRepository;

    public function __construct(Product $model, ProductAdditionalRepository $productAdditionalRepository, ProductGroupRepository $productGroupRepository)
    {
        $this->date = Carbon::now()->toDateTimeString();

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
        $prod = $this->model->with(['imgs' => function($query) {
                                return $query->orderBy('img_sort')->get();
                            }, 'specs.specPrices'])
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
                            ->where('prod_type', '!=', 4)
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->find($id);

        if (!$prod) return null;

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
                            ->when($onShelf, function($query){
                                $query->where('prod_onshelf', 1);
                            })
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
                            ->where('prod_onshelf_time', '<=', $this->date)
                            ->where('prod_offshelf_time', '>=', $this->date)
                            ->whereIn('prod_id', $idArray)
                            ->get();

        return $prods;
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
     * 取得根據
     * @params $suppliedId
     * @return Collections
     */
    public function supplierProducts(int $supplierId, $page_info)
    {
        $currentPage = $page_info['page'] ?? ProcuctConfig::DEFAULT_PAGE;
        $pageSize = $page_info['limit'] ?? ProcuctConfig::DEFAULT_PAGE_SIZE;

        Paginator::currentPageResolver(function () use ($currentPage) {
            return $currentPage;
        });

        return Product::where('supplier_id', $supplierId)
                    ->notDeleted()
                    ->where('prod_onshelf', 1)
                    ->where('prod_onshelf_time', '<', Carbon::now())
                    ->where('prod_offshelf_time', '>', Carbon::now())
                    ->with(['imgs' => function($query) {
                        $query->where('img_sort', 1);
                    }])
                    ->with(['product_tags' => function($query){
                        $query->where('is_main', 1);
                    }])
                    ->select(
                            'prod_id',
                            'prod_name',
                            'prod_price_sticker',
                            'prod_price_retail',
                            'prod_short',
                            'prod_store',
                            'prod_county',
                            'prod_district',
                            'prod_address'
                            )
                    ->paginate($pageSize);
    }
}
