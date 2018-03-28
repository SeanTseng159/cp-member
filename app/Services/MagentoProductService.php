<?php
/**
 * User: lee
 * Date: 2018/03/04
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\MagentoProductRepository;
use Ksd\Mediation\Services\ProductService;

class MagentoProductService
{

    protected $repository;

    public function __construct(MagentoProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增/更新 商品資料
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function createOrUpdate($sku, $data)
    {
        return $this->repository->createOrUpdate($sku, $data);
    }

    /**
     * 取得所有 商品資料
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function all($parameter)
    {
        return $this->repository->all($parameter);
    }

    /**
     * 取得單一 商品資料
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * 根據 所有id 取得對應商品明細
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function query($parameter)
    {
        return $this->repository->query($parameter);
    }

    /**
     * 更新 magento索引商品資料及更新商品快取
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function update($id)
    {
        $productService = app()->build(ProductService::class);

        $parameter = new \stdClass;
        $parameter->no = $id;
        $parameter->source = 'magento';
        $productService->cleanProductCache($parameter);
        return true;
    }

    /**
     * 取得所有 商品資料
     * @param $data
     * @return \App\Models\MagentoProduct
     */
    public function syncAll()
    {
        return $this->repository->syncAll();
    }
}
