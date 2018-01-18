<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/12/27
 * Time: 下午 05:42
 */

namespace Ksd\Mediation\Magento;

use App\Models\Member;
use GuzzleHttp\Exception\ClientException;
use Ksd\Mediation\Helper\ObjectHelper;
use App\Repositories\MemberRepository;
use App\Services\MemberService;

class Invoice extends Client
{
    use ObjectHelper;

    //bussiness setting
    const VAT_NUMBER = "53890045";              //賣方公司統編
    const BU_CODE     = "magento";               //賣方廠號
    const RECIPIENT_DIR = 'recipient/';           //發票目錄

    //sftp setting
    const SFTP_SERVER_IP    = 'localhost';          //發票上傳伺服器
    const SFTP_USER         = 'user';               //發票上傳帳號
    const SFTP_PASSWORD    = 'password';           //發票上傳密碼
    const UPLOAD_PATH      = 'Upload';             //發票上傳路徑
    const DOWNLOAD_PATH  = 'Download';           //發票回復檔下載路徑
    const BACKUP_PATH      = 'DownloadBackup';    //發票回復檔下載路徑

    private $memberRepository;


    private $member;

    public function __construct()
    {
        $this->member = new Member();
        parent::__construct();
    }


    /**
     * 取得十天前Complete訂單並組成發票格式
     * @return array
     */
    public function getOrdersBeforeTenDay()
    {

        $admintoken = new Client();
        $this->authorization($admintoken->token);

        $date = date("Y-m-d" , mktime(0,0,0,date("m"),date("d")-10,date("Y")) );
        $result = [];
        try {
            $path = 'V1/orders';
            $response = $this->putQuery('searchCriteria[filterGroups][0][filters][0][field]', 'status')
                ->putQuery('searchCriteria[filterGroups][0][filters][0][value]', 'pending')
                /*
                ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'updated_at ')
                ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $date)
                ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'from')
                ->putQuery('searchCriteria[filterGroups][4][filters][0][field]', 'updated_at ')
                ->putQuery('searchCriteria[filterGroups][4][filters][0][value]', $date)
                ->putQuery('searchCriteria[filterGroups][4][filters][0][condition_type]', 'to')
                */
                ->request('GET', $path);
            $body = $response->getBody();
            $result = json_decode($body, true);
        } catch (ClientException $e) {
            // TODO:抓不到MAGENTO API訂單資料
        }

        $data = [];
        if (!empty($result['items'])){
            foreach ($result['items'] as $item) {
                $data[] = $this->transInvoiceFormat($item);
            }
        }
        dd($data);
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

        $record_str .= self::VAT_NUMBER.'|';                  //10.賣方統一編號

        $record_str .= self::BU_CODE.'|';                      //11.賣方廠編

        $record_str .= '53890045'.'|';           //12.買方統一編號

        $record_str .= '高盛大'.'|';         //13.買受人公司名稱


        $member = $this->member->whereEmail($this->arrayDefault($result, 'customer_email'))->first();
//        $member = $this->memberRepository->findByEmail($this->arrayDefault($result, 'customer_email'));



        if(!empty($member)){

            $record_str .= $member->id.'|';                   //14.會員編號

            $record_str .= $member->name.'|';              //15.會員姓名

            $record_str .= $member->zipcode.'|';           //16.會員郵遞區號

            $record_str .= $member->county.                //17.會員地址
                $member->district.$member->address.'|';

            $record_str .= $member->cellphone.'|';         //18.會員電話

            $record_str .= $member->cellphone.'|';         //19.會員行動電話

            $record_str .= $member->email.'|';             //20.會員電子郵件

        }else{

            $record_str .= '|';                                 //15.會員姓名

            $record_str .= '|';                                 //16.會員郵遞區號

            $record_str .= '|';                                 //17.會員地址

            $record_str .= '|';                                 //18.會員電話

            $record_str .= '|';                                 //19.會員行動電話

            $record_str .= '|';                                 //20.會員電子郵件

        }


        $record_str .= '0|';                                    //21.紅利點數折扣金額,99999999.99999,無值帶 0

        $record_str .= 'N|';                                    //22.索取紙本發票,Y:紙本 N:非紙本(指電子發票)

        $record_str .= '|';                                     //23.發票捐贈註記

        $record_str .= '|';                                     //24.訂單註記

        $payment = $this->arrayDefault($result, 'payment');

        $payment_method = $this->paymentTypeTrans($payment['method']);


        $record_str .= $payment_method.'|';                     //25.付款方式

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




}