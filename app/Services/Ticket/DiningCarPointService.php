<?php

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarPointRecordRepository;
use App\Repositories\Ticket\DiningCarPointRuleRepository;

class DiningCarPointService extends BaseService
{
    protected $recordRepository;
    protected $ruleRepository;

    public function __construct(DiningCarPointRecordRepository $recordRepository, DiningCarPointRuleRepository $ruleRepository)
    {
        $this->recordRepository = $recordRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * 消費金額兌換點數
     * @param int $diningCarId
     * @return mixed
     */
    public function getExchangeRateRule($diningCarId = 0)
    {
        return $this->ruleRepository->findByType($diningCarId, 1);
    }

    /**
     * 消費金額兌換點數
     * @param int $diningCarId
     * @param int $memberId
     * @param int $consumeAmount
     * @return int [換得點數]
     */
    public function consumeAmountExchangePoint($diningCarId = 0, $memberId = 0, $consumeAmount = 0)
    {
        if ($consumeAmount <= 0) return 0;

        $rule = $this->getExchangeRateRule($diningCarId, 1);
        if (!$rule) return 0;

        // 寫入點數並記錄兌換
        return $this->recordRepository->saveExchangePoint($diningCarId, $memberId, $consumeAmount, $rule);
    }
}
