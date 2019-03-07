<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Helpers\ClientType;
use App\Helpers\ImageHelper;
use App\Models\MemberGiftItem;
use App\Repositories\BaseRepository;
use App\Services\ImageService;
use Carbon\Carbon;


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
        
        if ($client && $clientId)
        {
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
                    if ($type === 1)
                    {
                        $query->whereNull('used_time');
                    }
                    
                    return $query;
                })
            ->whereHas('gift',
                function ($q) use ($type, $clientObj) {
                    //取得期限內的 => 禮物狀態在resulte判斷 (todo 重購)
//                    if ($type === 1)
//                    {
//                        $q->where('start_at', '<=', Carbon::now())
//                            ->where('expire_at', '>=', Carbon::now());
//                    }
//                    //取得過期的或使用過的
//                    elseif ($type === 2)
//                    {
//                        $q->where('expire_at', '<', Carbon::now());;
//                    }
                    //取得某餐車的
                    if ($clientObj)
                    {
                        
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
    public function find($memberId, $memberGiftId)
    {
        return $this->memberGiftItem
            ->where('id', $memberGiftId)
            ->where('member_id', $memberId)
            ->with('gift', 'gift.diningCar')
            ->first();
    }
    
    
    public function update($memberId, $memberGiftId)
    {
        $row = $this->memberGiftItem
            ->where('id', $memberGiftId)
            ->where('member_id', $memberId)
            ->first();
        
        if ($row->used_time)
        {
            return false;
        }
        $result = $this->memberGiftItem
            ->where('id', $memberGiftId)
            ->where('member_id', $memberId)
            ->update(['used_time' => Carbon::now()]);
        
        return $result;
        
    }
    
    public function findByItemID($id)
    {
        return $this->memberGiftItem->find($id)->first();
        
    }
    
}
