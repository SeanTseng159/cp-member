<?php
/**
 * User: Lee
 * Date: 2018/01/14
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Repositories;

use Illuminate\Database\QueryException;
use Ksd\IPassPay\Models\IpasspayLog;
use Carbon\Carbon;

class IpasspayLogRepository
{
    protected $model;

    public function __construct(IpasspayLog $model)
    {
        $this->model = $model;
    }

    /**
     * 新增Log
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $ipasspayLog = new IpasspayLog();
            $ipasspayLog->fill($data);
            $ipasspayLog->save();
            return $ipasspayLog;
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 更新Log
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($orderId, $data)
    {
        try {
            $ipasspayLog = $this->model->where('order_id', $orderId)->first();

            if ($ipasspayLog) {
                $ipasspayLog->fill($data);
                $ipasspayLog->save();
                return $ipasspayLog;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * query
     * @param $data
     * @return mixed
     */
     public function queryOnlyOrderId($data, $datetime)
     {
         return $this->model->select('order_id')->where($data)->where('created_at', '>=', $datetime)->get();
     }
}
