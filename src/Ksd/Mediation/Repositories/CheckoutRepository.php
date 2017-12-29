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
    }

    /**
     * 取得結帳資訊
     * @param $source
     * @return array
     */
    public function info($source)
    {
        if($source === ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->info();
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
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->confirm($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->confirm($parameters);
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
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->transmit($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            return $this->cityPass->authorization($this->generateToken())->transmit($parameters);
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

        $data = $this->tspgPostbackService->find($parameters->order_no);

        //更新訂單狀態
        if ($data->order_source === "magento"){
            $this->magento->updateOrder($data,$parameters);
        }
        elseif ($data->order_source === "ct_pass"){
            $this->cityPass->updateOrder($parameters);
        }

        //依需求是否實作錯誤訊息
        $requestData=[
            'ErrorMessage'=>'付款失敗'
        ];

        $lang = env('APP_LANG');
        $url = env('CITY_PASS_WEB') . $lang;

        if ($data->order_device === '2') {

            $url = 'app://order?id=' . $data->order_id . '&source=' . $data->order_source;

            $url .= ($parameters->ret_code === "00") ? '&result=true&msg=success' : '&result=false&msg=' . $requestData['ErrorMessage'];

            $urldata = '<script>location.href="' . $url . '";</script>';
        }
        else {
            $s = ($data->order_source === 'ct_pass') ? 'c' : 'm';
            $url .= '/checkout/complete/' . $s . '/' . $parameters->order_id;
        }

        return ['urlData' => $url, 'platform' => $data->order_device];

    }

    /**
     * 接收台新信用卡後台通知程式 result_url
     * @param $parameters
     * @return array|mixed
     */
    public function result($parameters)
    {
        $file  = 'result.txt';
        file_put_contents($file, $parameters->ret_code,FILE_APPEND);
        return $parameters;

    }

    /**
     * 建立 token for citypass金流
     * @return string
     */
    public function generateToken()
    {
        $token = [
//            'exp' => time() + 7200,
            'secret' => 'a2f8b3503c2d66ea'
        ];

        return $this->JWTencode($token);
    }
}
