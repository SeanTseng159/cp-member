<?php
/**
 * User: lee
 * Date: 2018/11/23
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\SeqMenuOrder;
use DB;
use Illuminate\Database\QueryException;
use Exception;
use Carbon\Carbon;
use App\Core\Logger;

use App\Repositories\BaseRepository;


class SeqMenuOrderRepository extends BaseRepository
{
    protected $model;

    public function __construct(SeqMenuOrder $model)
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
            $seqOrder = $this->model->where('id', 1)->lockForUpdate()->first();

            $isToday = Carbon::parse($seqOrder->updated_at)->isToday();

            // 檢查最後修改日
            if (!$isToday) {
                // 重新計數
                $seqOrder->order_number = 1;
            } else {
                $seqOrder->order_number += 1;
            }

            $seqOrder->updated_at = date('Y-m-d H:i:s');

            $seqOrder->save();
            // DB::connection('backend')->commit();

            return 'M' . date("Ymd") . str_pad($seqOrder->order_number, 4, '0', STR_PAD_LEFT);
        } catch (QueryException $e) {
            Logger::error('SeqMenuOrderRepository getOrderNo Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return '';
        } catch (Exception $e) {
            Logger::error('SeqMenuOrderRepository getOrderNo Error', $e->getMessage());
            // DB::connection('backend')->rollBack();
            return '';
        }

    }
}
