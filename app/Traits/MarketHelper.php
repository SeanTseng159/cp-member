<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Traits;

trait MarketHelper
{
    /**
     * 取優惠條件規則
     * @param $data
     */
    private function getConditions($conditionType, $offerType, $conditions)
    {
        $newConditions = [];
        foreach ($conditions as $condition) {
            $newConditions[] = $this->getCondition($conditionType, $offerType, $condition);
        }

        return $newConditions;
    }

    /**
     * 取優惠條件規則
     * @param $data
     */
    private function getCondition($conditionType, $offerType, $condition)
    {
        $name = '';
        $type = '';

        // 優惠條件
        switch ($conditionType) {
            case 1:
                $name .= '滿%s元';
                $type .= 'DP';
                break;
            case 2:
                $name .= '滿%s件';
                $type .= 'DQ';
                break;
            case 3:
                $name .= '任選%s件';
                $type .= 'FQ';
                break;
        }

        // 優惠類型
        switch ($offerType) {
            case 1:
                $name .= ' 折%s元';
                $type .= 'FP';
                break;
            case 2:
                $name .= ' 打%s折';
                $type .= 'FD';
                break;
            case 4:
                $name .= ' %s元';
                $type .= 'FP';
                break;
        }

        $offer = $this->getOffer($offerType, $condition->offer);

        return [
            'name' => sprintf($name, $condition->condition, $offer),
            'type' => $type,
            'condition' => $condition->condition,
            'offer' => $offer
        ];
    }

    /**
     * 取優惠值
     * @param $data
     */
    private function getOffer($type, $offer)
    {
        if ($type === 2) {
            return round($offer, 2) * 100;
        }

        return floor($offer);
    }

    /**
     * 取最低優惠條件規則
     * @param $data
     */
    public function getLowerCondition($conditionType, $offerType, $conditions)
    {
        $rules = $this->getConditions($conditionType, $offerType, $conditions);

        foreach ($rules as $rule) {
            $nameAry[] = $rule['name'];
            $ruleType = $rule['type'];
        }

        $newRule = new \stdClass;
        $newRule->name = implode('，', $nameAry);
        $newRule->type = $ruleType;
        $newRule->lower = $conditions->min('condition');

        return $newRule;
    }
}
