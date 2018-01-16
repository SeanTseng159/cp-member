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
                    $body = $response->getBody()->getContents();
                    $data = [];
                    eval("\$data = $body;");
                    if (count($data) > 1) {
                        $parameter = [
                            'entity' => [
                                'entity_id' => intval($data[0]),
                                'increment_id' => $data[1],
                                'status' => 'processing',
                            ]
                        ];

                        $this->clear();
                        $this->putParameters($parameter);
                        $this->request('PUT', 'V1/orders/create');
                    }
                } catch (\Exception $e) {
                    Log::error('Magento tspgATMReturn fail: account='. $row->customerVirtualAccount);
                }
            }
        }
        return true;
    }
}