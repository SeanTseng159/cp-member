<?php
/**
 * User: lee
 * Date: 2018/11/23
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use DB;
use Illuminate\Database\QueryException;
use Exception;
use Carbon\Carbon;
use App\Core\Logger;

use App\Repositories\BaseRepository;
use App\Models\Ticket\SeqOrder;

class SeqOrderRepository extends BaseRepository
{
    public function __construct(SeqOrder $model)
    {
        $this->model = $model;
    }

    /**
     * 成立訂單編號
     * @return string
     */
    public function getOrderNo()
    {
        try {
            // DB::connection('backend')->beginTransaction();

            $seqOrder = $this->model->where('seq_order_id', 1)->lockForUpdate()->first();

            $isToday = Carbon::parse($seqOrder->modified_at)->isToday();

            // 檢查最後修改日
            if (!$isToday) {
                // 重新計數
                $seqOrder->sep_order_number = 1;
            }
            else {
                $seqOrder->sep_order_number += 1;
            }

            $seqOrder->modified_at = date('Y-m-d H:i:s');
            $seqOrder->save();
            // DB::connection('backend')->commit();

            return date("Ymd") . str_pad($seqOrder->sep_order_number, 5, '0', STR_PAD_LEFT);
        } catch (QueryException $e) {
            Logger::error('SeqOrder Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return '';
        } catch (Exception $e) {
            Logger::error('SeqOrder Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return '';
        }
    }
}
