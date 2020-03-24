<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\LayoutAdRepository as AdRepository;
use App\Repositories\Ticket\LayoutExplorationRepository as ExplorationRepository;
use App\Repositories\Ticket\LayoutHomeRepository as HomeRepository;
use App\Repositories\Ticket\TagRepository;
use App\Repositories\Ticket\TagProdRepository;
use App\Repositories\Ticket\LayoutCategoryRepository as CategoryRepository;
use App\Repositories\Ticket\MenuProductRepository;

use App\Repositories\Ticket\ProductRepository as ProductRepository;
use App\Config\Ticket\ProcuctConfig;
use App\Models\Ticket\Supplier as SupplierRepository;

use App\Repositories\Ticket\LayoutAppRepository as AppRepository;

class LayoutService extends BaseService
{
    protected $adRepository;
    protected $explorationRepository;
    protected $homeRepository;
    protected $tagRepository;
    protected $tagProductRepository;
    protected $categoryRepository;
    protected $menuProductRepository;
    protected $productRepository;
    protected $appRepository;

    public function __construct(AdRepository $adRepository, ExplorationRepository $explorationRepository, HomeRepository $homeRepository, TagRepository $tagRepository, TagProdRepository $tagProductRepository, CategoryRepository $categoryRepository, MenuProductRepository $menuProductRepository, ProductRepository $productRepository, AppRepository $appRepository)
    {
        $this->adRepository = $adRepository;
        $this->explorationRepository = $explorationRepository;
        $this->homeRepository = $homeRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
        $this->menuProductRepository = $menuProductRepository;
        $this->tagProductRepository = $tagProductRepository;
        $this->productRepository = $productRepository;

        $this->appRepository = $appRepository;
    }

    /**
     * 取首頁資料
     * @param $lang
     * @return mixed
     */
    public function home($lang = 'zh-TW')
    {
        $data['slide'] = $this->adRepository->getByArea(1, $lang);
        $data['banner'] = $this->adRepository->getByArea(2, $lang);
        $data['explorations'] = $this->explorationRepository->all($lang);
        $data['customizes'] = $this->homeRepository->all($lang);
        $data['activity'] = $this->appRepository->findInHome();

        return $data;
    }

    /**
     * 取選單資料
     * @param $lang
     * @return mixed
     */
    public function menu($lang = 'zh-TW')
    {
        return $this->tagRepository->all($lang);
    }

    /**
     * 取單一產品分類
     * @param $lang
     * @return mixed
     */
    public function productCategory($productId)
    {
        return   $this->tagProductRepository->getTagsByProdId($productId);
    }

    /**
     * 取單一選單資料
     * @param $lang
     * @param $id
     * @return mixed
     */
    public function oneMenu($lang = 'zh-TW', $id = 0)
    {
        return $this->tagRepository->one($lang, $id);
    }

    /**
     * 取單一熱門探索分類資料
     * @param $lang
     * @param $id
     * @return mixed
     */
    public function category($lang = 'zh-TW', $id = 0)
    {
        $data['category'] = $this->tagRepository->oneWithUpperId($lang, $id);
        $data['customizes'] = ($data['category']) ? $this->categoryRepository->allById($lang, $id) : [];

        return $data;
    }

    /**
     * 取熱門探索分類下所有商品
     * @param $lang
     * @param $id
     * @return mixed
     */
    public function categoryProducts($lang = 'zh-TW', $id = 0)
    {
        return $this->menuProductRepository->productsByTagUpperId($lang = 'zh-TW', $id);
    }

    /**
     * 取熱門探索子分類下所有商品
     * @param $lang
     * @param $id
     * @return mixed
     */
    public function subCategoryProducts($lang = 'zh-TW', $id = 0)
    {
        return $this->menuProductRepository->productsByTagId($lang = 'zh-TW', $id);
    }

    /**
     * 取供應商相關商品
     * @param int $supplierId
     * @param int $page
     * @param int $limit
     * @return type
     */
    public function supplierProducts($supplierId, $page = 1, $limit = 20)
    {
        $data['prods'] = $this->productRepository->supplierProducts($supplierId, $page, $limit);
        $data['supplier'] = SupplierRepository::find($supplierId);

        return $data;
    }
}
