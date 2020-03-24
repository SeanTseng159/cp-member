<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 3:08
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Magento\Checkout as MagentoCheckout;
use Ksd\Mediation\CityPass\Checkout as CityPassCheckout;
use App\Services\TspgPostbackService;
use App\Traits\JWTTokenHelper;
use Firebase\JWT\JWT;
use App\Models\TspgPostbackRecord;
use App\Jobs\Mail\OrderCreatedMail;
use App\Jobs\Mail\OrderPaymentCompleteMail;
use Ksd\Mediation\Cache\Key\OrderKey;
use Ksd\Mediation\Magento\Invoice as MagentoInvoice;
use log;
class CheckoutRepository extends BaseRepository
{
    use JWTTokenHelper;

    private $memberTokenService;
    private $tspgPostbackService;
    protected $model;

    public function __construct($memberTokenService,TspgPostbackService $tspgPostbackService)
    {
        $this->magento = new MagentoCheckout();
        $this->cityPass = new CityPassCheckout();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
        $this->tspgPostbackService = $tspgPostbackService;

        $this->setMemberId($this->memberTokenService->getId());
    }

    /**
     * 取得結帳資訊
     * @param $source
     * @return array
     */
    public function info($source)
    {
        if($source === ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info($this->memberTokenService->getId());
        } else if ($source === ProjectConfig::CITY_PASS) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->info();
        }
        return [];
    }

    /**
     * 設定物流方式
     * @param $parameters
     * @return bool
     */
    public function shipment($parameters)
    {
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->shipment($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {

        }else if ($parameters->checkSource(ProjectConfig::CITY_PASS_PHYSICAL)) {

        }else{
            return false;
        }
    }

    /**
     * 確定結帳
     * @param $parameters
     * @return array|mixed
     */
    public function confirm($parameters)
    {
        // 清掉訂單快取
        $this->redis->delete(sprintf(OrderKey::INFO_KEY, $this->memberId));
        $this->redis->delete(sprintf(OrderKey::MAGENTO_INFO_KEY, $this->memberId));

        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            $result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->confirm($parameters);

            // 寄送訂單成立mail，信用卡結帳不在此寄信
            if ($parameters->repay !== 'true' && $parameters->payment()->type !== 'credit_card' && $result) dispatch(new OrderCreatedMail($this->memberId, $parameters->source, $result['id']))->delay(5);

            return [
                'code' => ($result) ? '00000' : 'E9001',
                'data' => $result
            ];
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            Log::info('=== 傳送到CI建立訂單 ===');
            $result = $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->confirm($parameters);
            Log::info('=== CI回傳建立訂單 ===');
            Log::info($result);
            if ($result) {
                if ($parameters->repay !== 'true' && $result['statusCode'] === 201) dispatch(new OrderCreatedMail($this->memberId, $parameters->source, $result['data']['orderNo']))->delay(5);

                return [
                    'code' => $result['statusCode'],
                    'data' => $result['data']
                ];
            }

            return [
                'code' => 'E9001'
            ];
        }
    }

    /**
     * 信用卡送金流
     * @param $parameters
     * @return array|mixed
     */
    public function creditCard($parameters)
    {

        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->creditCard($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            return $this->cityPass->authorization($this->generateToken())->creditCard($parameters);
        }
    }

    /**
     * 信用卡送金流(台新)
     * @param $parameters
     * @return array|mixed
     */
    public function transmit($parameters)
    {
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            $result = $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->transmit($this->memberId, $parameters);

            // 信用卡結帳此時才拿到真正訂單ＩＤ，所以在這才寄送訂單成立mail
            if ($result) {
                dispatch(new OrderCreatedMail($this->memberId, $parameters->source, $result['id']))->delay(5);
            }

            return $result;
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            return $this->cityPass->authorization($this->generateToken())->transmit($this->memberId, $parameters);
        }
    }

    /**
     * 接收台新信用卡前台通知程式 post_back_url
     * @param $parameters
     * @return array|mixed
     */
    public function postBack($parameters)
    {
        \Log::debug('=== 台新回來 ===');
        \Log::debug(print_r($parameters, true));

        $record = [
            'ret_code' => $parameters->ret_code,
            'tx_type' => $parameters->tx_type,
            'order_no' => $parameters->order_no,
            'ret_msg' => $parameters->ret_msg,
            'auth_id_resp' => $parameters->auth_id_resp

        ];

        $pay = new TspgPostbackRecord();
        $pay->fill($record)->save();

        $data = $this->tspgPostbackService->find($parameters->order_no);

        $orderFlag = ($parameters->ret_code === "00");
        $updateData=[];
        //更新訂單狀態
        if ($data->order_source === ProjectConfig::MAGENTO){
            $this->magento->updateOrder($data,$parameters);
        }
        else if ($data->order_source === ProjectConfig::CITY_PASS){
            $this->cityPass->authorization($this->generateToken())->updateOrder($parameters);
        }else{
            $updateData=[
                'OrderMessage'=>'更新訂單失敗'
            ];
        }

        //依需求是否實作錯誤訊息
        $requestData=[
            'ErrorMessage'=>'付款失敗'
        ];
        $requestData=array_merge($updateData,$requestData);
        $lang = env('APP_LANG');
        $url = env('CITY_PASS_WEB') . $lang;

        if ($data->order_device === '2') {

            $url = 'app://order?id=' . $data->order_id . '&source=' . $data->order_source;

            $url .= ($parameters->ret_code === "00") ? '&result=true&msg=success' : '&result=false&msg=' . $requestData['ErrorMessage'];
        }
        else {
            $s = ($data->order_source === 'ct_pass') ? 'c' : 'm';
            if($s === 'm') {
                $url .= ($parameters->ret_code === "00") ? '/checkout/complete/' . $s . '/M0000' . $data->order_id : '/checkout/failure/000';
            }else{
                $url .= '/checkout/complete/' . $s . '/' . $data->order_id ;
            }
        }

        // 請求寄送訂單付款完成通知 (如付款失敗，則不發送)
        if ($orderFlag) {
            dispatch(new OrderPaymentCompleteMail($data->member_id, $data->order_source, $data->order_id))->delay(5);

            $invoice = new MagentoInvoice;
            $parameters = new \stdClass;
            $parameters->id = $data->order_id;
            $invoice->createMagentoInvoice($parameters);
        }

        return ['urlData' => $url, 'platform' => $data->order_device, 'orderFlag' => $orderFlag];
    }

    /**
     * 接收linepay前台通知程式
     * @param $parameters
     * @return array|mixed
     */
    public function feedback($parameters)
    {
        // 更新訂單狀態
        return $this->cityPass->authorization($this->generateToken())->linepayFeedback($parameters);
    }

    /**
     * 接收藍新bluenewpay 及taiwanpay 去更新order
     * @param $parameters
     * @return array|mixed
     */
    public function feedbackPay($parameters)
    {
        // 更新訂單狀態
        return $this->cityPass->authorization($this->generateToken())->payFeedback($parameters);
    }

    /**
     * 接收台新信用卡後台通知程式 result_url
     * @param $parameters
     * @return array|mixed
     */
    public function result($parameters)
    {
        return $this->magento->resultUrl($parameters);

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
