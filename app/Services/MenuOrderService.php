<?php

namespace App\Services;


use App\Repositories\MenuOrderRepository;
use Ksd\SMS\Services\EasyGoService;

class MenuOrderService extends BaseService
{
    /**
     * Default repository.
     *
     * @var string
     */
    protected $repository;

    public function __construct(MenuOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId = null)
    {
        return $this->repository->create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId);
    }

    public function get($menuOrderId)
    {
        return $this->repository->get($menuOrderId);
    }
    public function getByOrderNo($menuOrderNo)
    {
        return $this->repository->getByOrderNo($menuOrderNo);
    }

    public function getByCode($code)
    {
        return $this->repository->getByCode($code);
    }

    public function updateStatus($code, $status = false)
    {
        return $this->repository->updateStatus($code, $status);
    }

    public function memberList($memberId)
    {
        return $this->repository->memberList($memberId);
    }

    public function checkOrderProdStatus($memberId,$menuOrderNo){

        return $this->repository->checkOrderProdStatus($memberId,$menuOrderNo);
    }

    public function createOrder($params, $menuOrder)
    {
        return $this->repository->createOrder($params, $menuOrder);
    }

    public function sendSMS($shopName,$menuOrderNo,$code,$cellphone){
        try {
            $host = env("CITY_PASS_WEB");
            //發送簡訊
            $easyGoService = new EasyGoService;
            $phoneNumber = '+886' . substr($cellphone, 1, 9);
            $web = "{$host}order/{$code}";
            $message = "您好：您於{$shopName}點餐完成，編號{$menuOrderNo} {$web}";

            return $easyGoService->send($phoneNumber, $message);
        } catch (\Exception $e) {
            Logger::debug($e);
            return false;
        }
    }

}
