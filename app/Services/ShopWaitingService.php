<?php
/**
 * User: Annie
 * Date: 2019/09/24
 */

namespace App\Services;

use App\Core\Logger;
use App\Repositories\ShopWaitingRepository;
use Ksd\SMS\Services\EasyGoService;


class ShopWaitingService extends BaseService
{
    protected $repository;

    public function __construct(ShopWaitingRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function create($id, $name, $number, $cellphone, $memberID = null)
    {
        return $this->repository->create($id, $name, $number, $cellphone, $memberID);
    }

    public function sendWaitingSMS($host, $shopName, $shopId, $userName, $cellphone, $waitingId, $userWaitingNo)
    {
        try {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = '+886' . substr($cellphone, 1, 9);
            $web = "{$host}/shop/{$shopId}/waiting/{$waitingId}";
            $message = "{$userName}您好：您已成功候位{$shopName}，號碼{$userWaitingNo}，點擊連結查看 {$web}";
//            return $easyGoService->send($phoneNumber, $message);

        } catch (\Exception $e) {
            Logger::debug($e);
            return false;
        }
    }

    public function get($shopId,$waitingId)
    {
        return $this->repository->get($shopId,$waitingId);
    }

    public function delete($shopId,$waitingId)
    {
        return $this->repository->delete($shopId,$waitingId);
    }

}
