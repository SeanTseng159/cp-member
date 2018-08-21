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
use App\Repositories\Ticket\ProductRepository as ProductRepository;
use App\Config\Ticket\ProcuctConfig;
use App\Models\Ticket\Supplier as SupplierRepository;

class LayoutService extends BaseService
{
    protected $adRepository;
    protected $explorationRepository;
    protected $homeRepository;
    protected $productRepository;

    public function __construct(AdRepository $adRepository, ExplorationRepository $explorationRepository, HomeRepository $homeRepository, ProductRepository $productRepository)
    {
        $this->adRepository = $adRepository;
        $this->explorationRepository = $explorationRepository;
        $this->homeRepository = $homeRepository;
        $this->productRepository = $productRepository;
        
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

        return $data;
    }
    
    /**
     * 取供應商相關商品
     * @param int $supplierId
     * @param array $page_info
     * @return type
     */
    public function supplierProducts($supplierId, $page_info = [])
    {
        $data['prods'] = $this->productRepository->supplierProducts($supplierId, $page_info);
        $data['supplier'] = SupplierRepository::find($supplierId);
        
        return $data;
    }
}
