<?php
/**
 * User: Lee
 * Date: 2018/07/10
 * Time: 下午2:20
 */

namespace Ksd\Payment\Repositories;

use Ksd\Mediation\CityPass\Checkout as CityPassCheckout;
use Ksd\Payment\Models\Client;
use Exception;
use Log;


class LinePayRepository extends Client
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * reserve
     * @param $parameters
     * @return mixed
     */
    public function reserve($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'linepay/reserve');

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (ClientException $e) {
            Log::debug('=== linepay reserve error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== linepay reserve unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }
    
    /**
     * 接收linepay前台通知程式
     * @param $parameters
     * @return array|mixed
     */
    public function feedback($parameters)
    {
        \Log::debug('=== linepay回來 ===');
        \Log::debug(print_r($parameters, true));

        $record = [
            'order_no' => $parameters['data']->orderNo,
            'amount'   => $parameters['data']->amount,
            'status'   => $parameters['code'] === '00000' ? 1 : 0,
            'transactionId' => $parameters['data']->transactionId,
            'device'   => $parameters['device'],
        ];

//        $pay = new LinepayFeedbackRecord();
//        $pay->fill($record)->save();

        //更新訂單狀態
        $response = (new CityPassCheckout())->authorization($this->generateToken())->linepayFeedback($parameters);
        
return $response;
        
        // 請求寄送訂單付款完成通知 (如付款失敗，則不發送)
        if ($orderFlag) {
            dispatch(new OrderPaymentCompleteMail($data->member_id, $data->order_source, $data->order_id))->delay(5);

            $invoice = new MagentoInvoice;
            $parameters = new \stdClass;
            $parameters->id = $data->order_id;
            $invoice->createMagentoInvoice($parameters);
        }

        return [];

    }
    
    /**
     * 建立 token for citypass金流
     * @return string
     */
    public function generateToken()
    {
        $token = [
            'exp' => time() + 120,
            'secret' => 'a2f8b3503c2d66ea'
        ];

        return $this->JWTencode($token);
    }
}
