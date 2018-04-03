<?php
/**
 * User: lee
 * Date: 2018/04/03
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\MagentoInvoice;

class MagentoInvoiceRepository
{
    protected $model;

    public function __construct(MagentoInvoice $model)
    {
        $this->model = $model;
    }

    /**
     * 新增 發票
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $magentoInvoice = new MagentoInvoice();
            $magentoInvoice->fill($data);
            $magentoInvoice->save();
            return $magentoInvoice;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 更新 發票
     * @param $orderId
     * @param $orderNo
     * @param $data
     * @return mixed
     */
    public function update($orderId, $data)
    {
        try {
            $invoice = $this->find($orderId);

            if ($invoice) {
                $invoice->fill($data);
                $invoice->save();
                return $invoice;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 取得單一發票
     * @param $orderId
     * @return mixed
     */
    public function find($orderId)
    {
        return $this->model->where('order_id', $orderId)->first();
    }
}
