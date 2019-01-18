<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Traits;

trait CheckoutHelper
{

    /**
     * 計算運費
     * @param $shippingFeeModel [App\Models\Ticket\ShippingFee]
     * @param $quantity
     * @return int
     */
    private function calcShippingFee($shippingFeeModel, $quantity = 0)
    {
        if (!$shippingFeeModel || !$quantity) return 0;

        $fee = 0;
        $maxQuantity = 0;
        $maxfee = 0;
        foreach ($shippingFeeModel as $feeModel) {
            if ($quantity >= $feeModel->lower && $quantity <= $feeModel->upper) {
                $fee = $feeModel->fee;
            }

            if ($feeModel->upper > $maxQuantity) {
                $maxQuantity = $feeModel->upper;
                $maxfee = $feeModel->fee;
            }
        }

        // 如果數量大於最大運費數量, 則等於最大運費
        if ($quantity > $maxQuantity) $fee = $maxfee;

        return $fee;
    }
}
