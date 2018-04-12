<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/12/27
 * Time: 下午 05:42
 */

namespace Ksd\Mediation\Magento;

use GuzzleHttp\Exception\ClientException;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ksd\Mediation\Helper\ObjectHelper;
use App\Services\MagentoInvoiceService;
use Ksd\Mediation\Magento\Order;

class Invoice extends Client
{
    use ObjectHelper;

    //bussiness setting
    const VAT_NUMBER = "53890045";              //賣方公司統編
    const BU_CODE     = "magento";               //賣方廠號

    private $businessNo;
    private $member;
    private $invoiceService;

    public function __construct()
    {
        $this->businessNo = env('COMPANY_BUSINESS_NO', '53890045');
        $this->member = new Member();
        parent::__construct();

        $this->invoiceService = app()->build(MagentoInvoiceService::class);
    }


    /**
     * 取得十天前Complete訂單並組成發票格式
     * @return array
     */
    public function getOrdersBeforeTenDay()
    {
        $now = Carbon::now();
        $now->subDays(10);

        $startDate = $now->format('Y-m-d');
        $now->addDays(1);
        $endDate = $now->format('Y-m-d');

        $result = [];
        try {
            $path = 'V1/orders';
            $response = $this
                ->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'status')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', 'complete')
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
            // TODO:抓不到MAGENTO API訂單資料
            Log::debug($e);
        }

        $data = [];
        if (!empty($result['items'])){
            foreach ($result['items'] as $item) {
                $invoice = $this->transInvoiceFormat($item);
                if ($invoice) $data[] = $invoice;
            }
        }
        return $data;

    }

    /**
     * 發票檔格式
     * @param $result
     * @return string
     *
     */
    public function transInvoiceFormat($result)
    {
        // 檢查發票是否已寄送，送過就pass
        $orderId = $this->arrayDefault($result, 'entity_id');
        $check = $this->invoiceService->checkIsCreated($orderId);
        if ($check) return '';

        $record_str = "M|";                                     //1.主檔代號(M)

        $record_str .= $this->arrayDefault($result, 'increment_id').'|';                    //2.訂單編號

        $record_str .= '0|';                                    //3.訂單狀態:0.新增 1.修單 2.刪除 3.折讓

        $date_time = explode(" ",$this->arrayDefault($result, 'created_at'));
        $order_date = str_replace("-","/",$date_time[0]);
        $record_str .= $order_date.'|';                         //4.訂單日期

        $date_time = explode(" ",$this->arrayDefault($result, 'updated_at'));
        $order_date = str_replace("-","/",$date_time[0]);
        $record_str .= $order_date.'|';                         //5.預計出貨日期(繳費日期)

        $record_str .= '1|';                                    //6.稅率別 1.應稅 2.零稅率 3.免稅

        $record_str .= '|';                                     //7.訂單金額(未稅)(可不填)

        $record_str .= '|';                                     //8.訂單稅額(可不填)

        $record_str .= $this->arrayDefault($result, 'subtotal') + $this->arrayDefault($result, 'shipping_amount').'|';                //9.訂單金額(含稅)

        $record_str .= $this->businessNo.'|';                  //10.賣方統一編號

        $record_str .= self::BU_CODE.'|';                      //11.賣方廠編

        $invoiceTitle = '';
        $unifiedBusinessNo = '';
        // 找出三聯式發票
        $invoice = $this->invoiceService->find($orderId);
        if($invoice && $invoice->method == 2) {
            $invoiceTitle = $invoice->title;
            $unifiedBusinessNo = $invoice->ubn;
        }

        $record_str .= $unifiedBusinessNo .'|';   //12.買方統一編號
        $record_str .= $invoiceTitle.'|';         //13.買受人公司名稱
        
        // 找出會員資料
        $customer_email = $this->arrayDefault($result, 'customer_email');
        $member = $this->member->where('email', $customer_email)->first();

        // 找不到會員資料，找第三方登入帳號
        if (!$member) {
            $oauthAcc = explode("_", $customer_email);
            $member = $this->member->where('openPlateform', $oauthAcc[0])->where('openId', $oauthAcc[1])->first();
        }

        if($member){
            $email = $member->email ?: $member->openId;

            $record_str .= $member->id . '|';                //14.會員編號

            $record_str .= $member->name . '|';              //15.會員姓名

            $record_str .= $member->zipcode . '|';           //16.會員郵遞區號

            $record_str .= $member->address . '|';           //17.會員地址

            $record_str .= '+' . $member->countryCode . $member->cellphone . '|';  //18.會員電話

            $record_str .= '+' . $member->countryCode . $member->cellphone . '|';  //19.會員行動電話

            $record_str .= $email . '|';             //20.會員電子郵件
        } else {
            return '';
        }


        $record_str .= '0|';                                    //21.紅利點數折扣金額,99999999.99999,無值帶 0

        $record_str .= 'N|';                                    //22.索取紙本發票,Y:紙本 N:非紙本(指電子發票)

        $record_str .= '|';                                     //23.發票捐贈註記

        $record_str .= '|';                                     //24.訂單註記

        $payment = $this->arrayDefault($result, 'payment');

        $payment_method = $this->paymentTypeTrans($payment['method']);


        $record_str .= $payment_method . '|';                     //25.付款方式

        $record_str .= '|';                                     //26.相關號碼 1(出貨單號),出貨單號顯示在紙本發票列印樣式右邊

        $record_str .= '|';                                     //27.相關號碼 2 訂單狀態為折讓，此為必填欄位(不可重複) (相關號碼 2=退貨單號=折讓單號)

        $record_str .= '|';                                     //28.相關號碼 3 若有「強制退款」請註記在此欄位

        $record_str .= '|';                                     //29.主檔備註 顯示在紙本發票右邊(可帶商品資訊)

        $record_str .= '|';                                     //30.商品名稱 若有帶值,則不允許傳訂單明細檔。若沒帶
        //            值,則看體系設定檔有無設定值。

        $record_str .= '|';                                     //31.載具類別號碼 1.手機條碼:3J0002(如手機條碼)
        //                2.會員載具:EJ0047 (使用金財通)

        $record_str .= '|';                                     //32.載具顯碼 id1(明碼) 1.xxyu/123
        //                      2.會員編號=手機號碼

        $record_str .= '|';                                     //33.載具隱碼 id2(內碼) 1.xxyu/123
        //                      2.會員編號=手機號碼

        $record_str .= '|';                                     //34.發票號碼

        $record_str .= '|';                                     //35.隨機碼

        $record_str .= "\r\n"; //CR+LF

        // 註記該發票已開立
        $this->invoiceService->update($orderId, [
            'status' => 1
        ]);
        
        return $record_str;
    }

    /**
     * 付款方式名稱轉換
     * @param $key
     * @return string
     */
    public function paymentTypeTrans($key)
    {
        if (!empty($key)) {
           if ($key === "tspg_atm") {
                return "ATM虛擬帳號";
            } else if ($key === "tspg_transmit") {
                return "信用卡";
            } else if ($key === "ipasspay") {
                return "iPassPay電子餘額支付";
            }
        } else {
            return "";
        }

    }

    /**
     * 自動開立magento發票
     * @param $parameters
     * @return boolean
     */
    public function createMagentoInvoice($parameters)
    {
        try {
            \Log::info('=== 自動開立magento發票 ===');
            
            $order = (new Order)->find($parameters);
            $order = (isset($order[0]) && $order[0]) ? $order[0] : null;

            if (!$order || $order->orderStatusCode !== '01') return false;

            $parameter = [
                'capture' => true,
                'notify' => false
            ];
            $this->putParameters($parameter);

            $path = sprintf('V1/order/%s/invoice', $parameters->id);
            $response = $this->request('POST', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);

            \Log::debug(print_r($result, true));

            return (isset($result));
        } catch (ClientException $e){
            return false;
        }
    }
}