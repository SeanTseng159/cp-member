<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use DB;
use Illuminate\Database\QueryException;
use Exception;
use App\Core\Logger;
use App\Repositories\BaseRepository;
use App\Models\Ticket\MemberGiftItem;


class MemberGiftItemRepository extends BaseRepository
{
    private $limit = 20;

    public function __construct(MemberGiftItem $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     *
     * @param int $memberId
     * @param int $giftId
     * @param int $number
     *
     * @return mixed
     */
    public function create($memberId = 0, $giftId = 0, $number = 1)
    {
        try {
            if ($this->find($memberId, $giftId, $number)) return null;

            DB::connection('backend')->beginTransaction();

            $model = new MemberGiftItem;
            $model->member_id = $memberId;
            $model->gift_id = $giftId;
            $model->number = $number;
            $model->save();

            DB::connection('backend')->commit();

            return $model;
        } catch (QueryException $e) {
            Logger::error('QueryException Create MemberGiftItem Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            return null;
        } catch (Exception $e) {
            Logger::error('Exception Create MemberGiftItem Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            return null;
        }
    }

    /**
     * 查單一筆
     *
     * @param int $memberId
     * @param int $giftId
     * @param int $number
     *
     * @return mixed
     */
    public function find($memberId = 0, $giftId = 0, $number = 0)
    {
        return $this->model->where('member_id', $memberId)
                            ->where('gift_id', $giftId)
                            ->where('number', $number)
                            ->first();
    }
}
