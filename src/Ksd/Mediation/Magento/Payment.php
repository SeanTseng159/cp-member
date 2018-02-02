<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/16
 * Time: 上午 11:25
 */

namespace Ksd\Mediation\Magento;


use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                    $data = json_decode($body, true);
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

    public function tspgATMOrderStatusProcess(){
        $now = Carbon::now();
        $now->subDays(15);

        $startDate = $now->format('Y-m-d');
        $now->addDays(15);
        $endDate = $now->format('Y-m-d');
        $result = [];

        try {
            $path = 'V1/orders';
            $response = $this
                ->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'status')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', 'pending')
                ->putQuery('searchCriteria[filterGroups][1][filters][0][field]', 'updated_at')
                ->putQuery('searchCriteria[filterGroups][1][filters][0][value]', $startDate)
                ->putQuery('searchCriteria[filterGroups][1][filters][0][condition_type]', 'from')
                ->putQuery('searchCriteria[filterGroups][2][filters][0][field]', 'updated_at')
                ->putQuery('searchCriteria[filterGroups][2][filters][0][value]', $endDate)
                ->putQuery('searchCriteria[filterGroups][2][filters][0][condition_type]', 'to')
                ->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);

        } catch (\Exception $e) {
            Log::error('Magento tspgATMOrderCheck fail');
        }

        if (!empty($result['items'])){
            foreach ($result['items'] as $item) {
                if(isset($item['payment']['additional_information'])) {
                    if ($item['payment']['additional_information'][0] === 'Tspg Atm Payment') {
                        $parameter = [
                            'entity' => [
                                'entity_id' => intval($item['entity_id']),
                                'increment_id' => $item['increment_id'],
                                'status' => 'closed',
                            ]
                        ];
                        $this->clear();
                        $this->putParameters($parameter);
                        $this->request('PUT', 'V1/orders/create');

                    }
                }
            }
        }
        return true;


    }
}