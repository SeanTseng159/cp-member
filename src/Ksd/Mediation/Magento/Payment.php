<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/16
 * Time: 上午 11:25
 */

namespace Ksd\Mediation\Magento;


use Illuminate\Support\Facades\Log;

class Payment extends Client
{
    public function tspgATMReturn($result)
    {
        if (!empty($result) && count($result) > 0) {
            foreach ($result as $row) {
                try {
                    $this->clear();
                    $response = $this->putParameter('account', $row->customerVirtualAccount)
                        ->request('POST', 'V1/ksd/order/payment');
                    $saleOrderId = $response->getBody()->getContents();


                    $parameter = [
                        'entity' => [
                            'entity_id' => intval($saleOrderId),
                            'status' => 'processing',

                        ]
                    ];


                    $this->clear();
                    $this->putParameters($parameter);
                    $this->request('PUT', 'V1/orders/create');
                } catch (\Exception $e) {
                    Log::error('Magento tspgATMReturn fail: account='. $row->customerVirtualAccount);
                }
            }
        }
        return true;
    }
}