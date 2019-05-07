<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Core\Logger;
use App\Enum\ClientType;
use App\Enum\GiftType;
use App\Models\Gift;
use App\Models\MemberGiftItem;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Matrix\trace;


class GiftRepository extends BaseRepository
{
    private $limit = 20;
    protected $missionModel;
    protected $memberGiftItem;

    public function __construct(Gift $model,MemberGiftItem $memberGiftItem)
    {
        $this->missionModel = $model;
        $this->memberGiftItem = $memberGiftItem;
    }

    /**
     * 依類型取詳細gift資料
     *
     * @param string $modelType
     * @param int $modelSpecId
     * @param string $type ['join', 'birthday', 'point']
     *
     * @return mixed
     */
    public function findByType($modelType = '', $modelSpecId = 0, $type = '')
    {
        return $this->missionModel->where('model_type', $modelType)
            ->where('model_spec_id', $modelSpecId)
            ->where('type', $type)
            ->isActive()
            ->first();
    }


    /**
     * 取特定餐車會員的禮物數
     *
     * @param int $memberId
     * @param int $diningCarId
     *
     * @return mixed
     */
    public function getMemberGiftItemsCountByDiningCarId($memberId = 0, $diningCarId = 0)
    {
        return $this->missionModel->join('member_gift_items', function ($join) use ($memberId) {
            $join->on('gifts.id', '=', 'member_gift_items.gift_id')
                ->where('member_gift_items.member_id', '=', $memberId);
        })
            ->where('model_spec_id', $diningCarId)
            ->select('id')
            ->count();
    }

    /**
     * 取得店家上架的禮物清單
     * @param $modelType
     * @param $modeSpecId
     *
     * @return mixed
     */
    public function list($modelType, $modeSpecId)
    {
        $client = ClientType::transform($modelType);
        $result = $this->missionModel->where('model_type', $client)
            ->where('model_spec_id', $modeSpecId)
            ->where('type', GiftType::point)
            ->isActive()
            ->orderBy('sort')
            ->get(['id', 'name', 'points', 'qty', 'limit_qty', 'desc', 'expire_at', 'content']);

        return $result;
    }

    /**
     * 取得某餐車的禮物資訊
     * @param $giftId
     * @return mixed
     */
    public function getWithDiningCar($giftId)
    {
        return $this->missionModel
            ->exchangable()
            ->isDiningCar()
            ->where('id', $giftId)
            ->first();

    }


    /**
     * 取得發送生日禮的會員資料
     * @param $dateDiff
     * @return mixed
     */
    public function getBirthdayDingingMembers($dateDiff)
    {
        $result = $this->missionModel
            ->exchangable()
            ->isDiningCar()
            ->with(
                [
                    'diningCar',
                    'diningCar.members',
                    'diningCar.members.member' => function ($q) use ($dateDiff) {
                        $q->whereRaw("DATE_FORMAT(birthday,'%m-%d') BETWEEN DATE_FORMAT(NOW(), '%m-%d') AND DATE_FORMAT(DATE_ADD(NOW(), INTERVAL $dateDiff DAY),'%m-%d')");

                    }
                ]
            )
            ->where('type', GiftType::birthday)
            ->get();
        return $result;


    }

    public function deliveryGifts($gifts, $memberGiftItems)
    {
        try {
//            dd($gifts);
//            dd($memberGiftItems);
            DB::connection('backend')->beginTransaction();

            $this->memberGiftItem->insert($memberGiftItems);

            //update禮物庫存量
           foreach ($gifts as $gift) {
               $qty = $gift->qty;
               $id = $gift->gift_id;
               $this->missionModel->where('id', $id)->update(['qty' => $qty]);
           }

            DB::connection('backend')->commit();
        }
        catch (\Exception $e) {
            Logger::error('deliveryGifts: ', $e->getMessage());
            DB::connection('backend')->rollBack();
        }
    }
}
