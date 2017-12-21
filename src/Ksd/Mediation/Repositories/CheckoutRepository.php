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
class CheckoutRepository extends BaseRepository
{
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
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->creditCard($parameters);
        }
    }

    /**
     * 信用卡送金流
     * @param $parameters
     * @return array|mixed
     */
    public function transmit($parameters)
    {
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->transmit($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->creditCard($parameters);
        }
    }

    /**
     * 接收台新信用卡前台通知程式 post_back_url
     * @param $parameters
     * @return array|mixed
     */
    public function postBack($parameters)
    {
        $data = $this->tspgPostbackService->find($parameters->order_no);

        $requestData=[];
        $lang = 'zh_TW';
        $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
        $url .= $lang;

        if ($data->order_device === '2') {

            $url = 'app://order?id=' . $data->order_no . '&source=' . $data->order_source;

            $url .= isset($data) ? '&result=true&msg=success' : '&result=false&msg=' . $requestData['ErrorMessage'];

            $urldata = '<script>location.href="' . $url . '";</script>';
            return ['urlData' => $urldata,'platform' => $data->order_device];

        }
        else {
            $s = ($data->order_source === 'ct_pass') ? 'c' : 'm';
            $url .= '/checkout/complete/' . $s . '/' . $data->order_no;

            return ['urlData' => $url,'platform' => $data->order_device];
        }



        //處理網頁拋轉至訂單完成頁
        $lang = 'zh_TW';
        $url = (env('APP_ENV') === 'production') ? env('CITY_PASS_WEB') : 'http://localhost:3000/';
        $url .= $lang;
        $s = (strstr($parameters->order_no,0,1) === '0') ? 'm' : 'c';
        $url .= '/checkout/complete/' . $s . '/' . $parameters->order_no;


        $file  = 'postBack.txt';
        file_put_contents($file, $parameters->ret_code,FILE_APPEND);
        return $parameters;

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
}