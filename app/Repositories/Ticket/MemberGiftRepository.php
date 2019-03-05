<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Helpers\ClientType;
use App\Models\Gift;
use App\Models\Member;
use App\Models\MemberGiftItem;
use App\Models\Ticket\DiningCar;
use App\Repositories\BaseRepository;


class MemberGiftRepository extends BaseRepository
{
    
    private $gift;
    private $memberGiftItem;
    private $diningCar;
    private $memeber;
    
    public function __construct(Gift $gift,
                                MemberGiftItem $memberGiftItem,
                                DiningCar $car,
                                Member $member)
    {
        $this->gift = $gift;
        $this->memberGiftItem = $memberGiftItem;
        $this->diningCar = $car;
        $this->memeber = $member;
        
    }
    
    /** 取得使用者之禮物列表，如果$client與$clientID非null，則取得該餐車的資料即可
     *
     * @param        $type :0:可使用/1:已使用/過期
     * @param        $memberId
     *
     * @param        $client
     * @param        $clientId
     *
     * @return mixed
     */
    public function list($type,
                         $memberId,
                         $client,
                         $clientId)
    {
//        [
//            'id'       => 1,
//            'Name'     => '大碗公餐車',
//            'title'    => '日本和牛丼飯 一份',
//            'duration' => '2019-1-31',
//            'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
//            'status'   => 0,
//        ],
        $clientObj = null;
        if ($client && $clientId)
        {
            $clientObj = new \stdClass();
            $clientObj->clientType = ClientType::transform($client);
            $clientObj->clientId = $clientId;
        }
        
        
        //會員的所有禮物
        $memberGifts = $this->memeber->find($memberId)->gifts(1)->get();
        
        dd($memberGifts);
        
        return $memberGifts;
        
    }
    
    
}
