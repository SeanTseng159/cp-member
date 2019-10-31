<?php
/**
 * User: Annie
 * Date: 2019/09/24
 */

namespace App\Services;

use App\Core\Logger;
use App\Repositories\ShopWaitingRepository;
use Carbon\Carbon;
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

    public function getByCode($code)
    {
        return $this->repository->getByCode($code);
    }


    public function create($id, $name, $number, $cellphone, $memberID = null)
    {
        return $this->repository->create($id, $name, $number, $cellphone, $memberID);
    }

    public function sendWaitingSMS($host, $shopName, $userName, $cellphone, $userWaitingNo, $code)
    {
        try {
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = '+886' . substr($cellphone, 1, 9);
            $web = "{$host}lineup/{$code}";
            $message = "{$userName}您好：您已成功候位{$shopName}，號碼{$userWaitingNo} {$web}";
            return $easyGoService->send($phoneNumber, $message);
        } catch (\Exception $e) {
            Logger::debug($e);
            return false;
        }
    }

    public function get($shopId, $waitingId)
    {
        return $this->repository->get($shopId, $waitingId);
    }

    public function delete($shopId, $waitingId, $memberId)
    {
        return $this->repository->delete($shopId, $waitingId, $memberId);
    }

    public function deleteByCode($code)
    {
        return $this->repository->deleteByCode($code);
    }

    public function getWaitingNumber($shopId, $waitingNo)
    {
        return $this->repository->getWaitingNumber($shopId, $waitingNo);
    }

    public function getMemberList($memberId, $page)
    {
        return $this->repository->getMemberList($memberId, $page);
    }

    public function getMemberListPageCount($memberId)
    {
        return $this->repository->getMemberListPageCount($memberId);
    }

    public function decode($code)
    {
        return $this->repository->decode($code);
    }
}
