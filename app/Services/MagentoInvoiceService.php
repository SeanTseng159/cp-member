<?php
/**
 * User: lee
 * Date: 2018/04/03
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\MagentoInvoiceRepository;

class MagentoInvoiceService
{

    protected $repository;

    public function __construct(MagentoInvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增 發票
     * @param $data
     * @return \App\Models\MagentoInvoice
     */
    public function create($data)
    {
        return $this->repository->create($data);
    }

    /**
     * 更新 發票
     * @param $orderId
     * @param $data
     * @return \App\Models\MagentoInvoice
     */
    public function update($orderId, $data)
    {
        return $this->repository->update($orderId, $data);
    }

    /**
     * 取得單一發票
     * @param $orderId
     * @return \App\Models\MagentoInvoice
     */
    public function find($orderId)
    {
        return $this->repository->find($orderId);
    }

    /**
     * 確認發票是否已開立
     * @param $orderId
     * @return boolean
     */
    public function checkIsCreated($orderId)
    {
        $invoice = $this->repository->find($orderId);

        if ($invoice) {
            return ($invoice->status !== 0);
        }

        return false;
    }
}
