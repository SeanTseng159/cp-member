<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Enum\ClientType;
use App\Enum\GiftType;
use App\Models\MemberGiftItem;
use App\Repositories\BaseRepository;
use App\Services\ImageService;
use Carbon\Carbon;
use DB;
use App\Core\Logger;


class MemberGiftItemRepository extends BaseRepository
{
    private $limit = 20;

    private $memberGiftItem;
    private $imageService;


    public function __construct(MemberGiftItem $memberGiftItem, ImageService $imageService)
    {
        $this->memberGiftItem = $memberGiftItem;
        $this->imageService = $imageService;
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
            if ($this->find($memberId, $giftId, $number)) {
                return null;
            }

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
        return $this->memberGiftItem->where('member_id', $memberId)
            ->where('gift_id', $giftId)
            ->where('number', $number)
            ->first();
    }


    /** 取得使用者之禮物列表，如果$client與$clientID非null，則取得該餐車的資料即可
     *
     * @param        $type :0:可使用/1:已使用or過期
     * @param        $memberId
     *
     * @param        $client
     * @param        $clientId
     *
     * @return mixed
     */
    public function list($type, $memberId, $client, $clientId)
    {
        $clientObj = null;

        if ($client && $clientId) {
            $clientObj = new \stdClass();
            $clientObj->clientType = ClientType::transform($client);
            $clientObj->clientId = $clientId;
        }


        //會員的所有禮物
        return $this->memberGiftItem
            ->byUser($memberId)
            ->when($type,
                function ($query) use ($type) {
                    //禮物未使用
                    if ($type === 1) {
                        $query->whereNull('used_time');
                    }

                    return $query;
                })
            ->whereHas('gift',
                function ($q) use ($type, $clientObj) {
                    //取得某餐車的
                    if ($clientObj) {

                        $q->where('model_type', $clientObj->clientType)
                            ->where('model_spec_id', $clientObj->clientId);
                    }

                    return $q->where('status', 1);
                })
            ->with('gift')
            ->whereHas('gift.diningCar',
                function ($q) {
                    //餐車是enabled
                    $q->where('status', 1);
                })
            ->with('gift.diningCar')
            ->get();


    }

    /**
     * 禮物詳細資料
     *
     * @param $memberId
     * @param $memberGiftId
     *
     * @return
     */
    public function findByGiftId($memberId, $giftID)
    {
        $result = $this->memberGiftItem
            ->where('member_id', $memberId)
            ->where('gift_id',$giftID)
            ->with(['gift','gift.diningCar'])
            ->first();

        return $result;
    }





    public function update($memberId, $memberGiftId)
    {
        $row = $this->memberGiftItem
            ->where('id', $memberGiftId)
            ->where('member_id', $memberId)
            ->first();

        if ($row->used_time) {
            return false;
        }
        $result = $this->memberGiftItem
            ->where('id', $memberGiftId)
            ->where('member_id', $memberId)
            ->update(['used_time' => Carbon::now()]);

        return $result;

    }

    /**
     * 取得特定禮物的使用數
     * @param array $giftIds
     * @return mixed
     */
    public function getUsedCount(array $giftIds)
    {
        $result = $this->memberGiftItem
            ->select('member_id', 'gift_id', DB::raw('count(*) as total'))
            ->groupBy('member_id', 'gift_id')
            ->whereIn('gift_id', $giftIds)
            ->get();

        return $result;
    }

    public function getUserAvailableGiftCount($memberId, $modelType, $modelSepcID)
    {


        $result = $this->memberGiftItem
            ->with(['gifts' => function ($query) use ($modelType, $modelSepcID) {
                return $query
                    ->where('model_type', $modelType)
                    ->where('model_spec_id', $modelSepcID);
            }])
            ->where('member_id', $memberId)
            ->whereNull('used_time')
            ->count();

        return $result;

    }
}
