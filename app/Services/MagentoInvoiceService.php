<?php
/**
 * User: lee
 * Date: 2018/04/03
 * Time: 上午 9:42
 */

namespace App\Services;

use Carbon\Carbon;
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

        return true;
    }

    /**
     * 確認發票是否已作廢
     * @param $orderId
     * @return boolean
     */
    public function checkIsInvalid($orderId)
    {
        $invoice = $this->repository->find($orderId);

        if ($invoice) {
            return ($invoice->status === 2 || $invoice->status === 3);
        }

        return true;
    }

    /**
     * 取得發票作廢或折讓
     * @param $orderId
     * @return string
     */
    public function getStatusIsInvalidOrDebit($orderId)
    {
        $invoice = $this->repository->find($orderId);

        if ($invoice && $invoice->status === 1) {
            $now = Carbon::now();
            $dt = Carbon::parse($invoice->updated_at);
            return ($now->diffInMonths($dt)) ? '3' : '2';
        }

        return null;
    }
}
