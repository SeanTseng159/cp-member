<?php
/**
 * User: lee
 * Date: 2018/08/27
 * Time: 上午 10:03
 */

namespace App\Traits;

use Carbon\Carbon;

trait InvoiceHelper
{

    // 賣方公司統編
    private $businessNo = "53890045";
    // 賣方廠編
    private $businessCode = 'ct_pass';

    /**
     * 發票主檔格式
     * @param $order
     * @param $status
     * @param $isDel
     * @return string
     *
     */
    public function transMainInvoiceFormat($order, $status = null, $isDel = false)
    {
        if (is_null($status)) return '';

        // 金額0直接跳出, 等於不開發票
        if ($order->recipientAmount <= 0 && !$isDel) return '';

        $orderPrefix = (env('APP_ENV') === 'production') ? '' : 'test_';

        //1.主檔代號(M)
        $recordStr[] = 'M';

        //2.訂單編號
        $recordStr[] = $orderPrefix . $order->order_no;

        //3.訂單狀態:0.新增 1.修單 2.刪除 3.折讓
        $recordStr[] = $status;

        //4.訂單日期
        $createdAt = Carbon::parse($order->created_at);
        $createdAtDate = $createdAt->format('Y/m/d');
        $recordStr[] = $createdAtDate;

        //5.預計出貨日期(繳費日期)
        $paidAt = Carbon::parse($order->order_paid_at);
        $paidAtDate = $paidAt->format('Y/m/d');
        $recordStr[] = $paidAtDate;

        //6.稅率別 1.應稅 2.零稅率 3.免稅
        $recordStr[] = '1';

        //7.訂單金額(未稅)(可不填)
        $recordStr[] = '';

        //8.訂單稅額(可不填)
        $recordStr[] = '';

        //9.訂單金額(含稅)
        $recordStr[] = $isDel ? 0 : $order->recipientAmount;

        //10.賣方統一編號
        $recordStr[] = $this->businessNo;

        //11.賣方廠編
        $recordStr[] = $this->businessCode;

        //12.買方統一編號
        $recordStr[] = $order->order_receipt_ubn;
        //13.買受人公司名稱
        $recordStr[] = $order->order_receipt_title;

        // 會員資料
        if ($order->member) {
            //14.會員編號
            $recordStr[] = $order->member->id;
            //15.會員姓名
            $recordStr[] = $order->member->name;
            //16.會員郵遞區號
            $recordStr[] = $order->member->zipcode;
            //17.會員地址
            $recordStr[] = $order->member->address;
            //18.會員電話
            //19.會員行動電話
            $phone = '+' . $order->member->countryCode . $order->member->cellphone;
            $recordStr[] = $phone;
            $recordStr[] = $phone;
            //20.會員電子郵件
            $recordStr[] = $order->member->email;
        }
        else {
            //14.會員編號
            $recordStr[] = '';
            //15.會員姓名
            $recordStr[] = '';
            //16.會員郵遞區號
            $recordStr[] = '';
            //17.會員地址
            $recordStr[] = '';
            //18.會員電話
            //19.會員行動電話
            $recordStr[] = '';
            $recordStr[] = '';
            //20.會員電子郵件
            $recordStr[] = '';
        }

        //21.紅利點數折扣金額,99999999.99999,無值帶 0
        $recordStr[] = '0';
        //22.索取紙本發票,Y:紙本 N:非紙本(指電子發票)
        $recordStr[] = 'N';
        //23.發票捐贈註記
        $recordStr[] = '';
        //24.訂單註記
        $recordStr[] = '';
        //25.付款方式
        $recordStr[] = $this->getPaymentMethod($order->order_payment_method);
        //26.相關號碼 1(出貨單號)
        $recordStr[] = '';
        //27.相關號碼 2 訂單狀態為折讓，此為必填欄位(不可重複) (相關號碼 2=退貨單號=折讓單號)
        $recordStr[] = ($status == 3) ? $order->refundId : '';
        //28.相關號碼 3 若有「強制退款」請註記在此欄位
        $recordStr[] = '';
        //29.主檔備註 顯示在紙本發票右邊(可帶商品資訊)
        $recordStr[] = '';
        //30.商品名稱 若有帶值,則不允許傳訂單明細檔。若沒帶值,則看體系設定檔有無設定值。
        $recordStr[] = '';
        //31.載具類別號碼 1.手機條碼:3J0002(如手機條碼)
        //              2.會員載具:EJ0047 (使用金財通)
        $recordStr[] = '';
        //32.載具顯碼 id1(明碼) 1.xxyu/123
        //                    2.會員編號=手機號碼
        $recordStr[] = '';
        //33.載具隱碼 id2(內碼) 1.xxyu/123
        //                    2.會員編號=手機號碼
        $recordStr[] = '';
        //34.發票號碼
        $recordStr[] = '';
        //35.隨機碼
        $recordStr[] = '';

        $recordStr = implode('|', $recordStr);
        //換行
        $recordStr .= "\r\n";

        return $recordStr;
    }

    /**
     * 發票商品明細檔格式
     * @param $result
     * @return string
     *
     */
    public function transDetailInvoiceFormat($order, $isDel = false)
    {
        $detailInvoices = [];

        $orderPrefix = (env('APP_ENV') === 'production') ? '' : 'test_';

        $i = 1;
        foreach ($order->detail as $detail) {
            // 排除 子商品以及發票金額為0 的項目
            if ($detail->prod_type == 4 || ($detail->recipient_price <= 0 && !$isDel)) continue;

            // 商品名稱
            if ($detail->productSpecPrice->prod_spec_price_recipient_type == 1) {
                $detail->prod_spec_name .= '(平台服務費)';
            }

            //1.明細代號(D)
            $recordStr[] = 'D';
            //2.序號
            $recordStr[] = $i;
            //3.訂單編號
            $recordStr[] = $orderPrefix . $order->order_no;
            //4.商品編號
            $recordStr[] = $detail->prod_cust_id;
            //5.商品條碼
            $recordStr[] = '';
            //6.商品名稱
            $recordStr[] = $detail->prod_name . '-' . $detail->prod_spec_name;
            //7.商品規格
            $recordStr[] = '';
            //8.單位
            $recordStr[] = '';
            //9.單價
            $recordStr[] = '';
            //10.數量
            $recordStr[] = 1;
            //11.未稅金額
            $recordStr[] = '';
            //12.含稅金額
            $recordStr[] = $isDel ? 0 : $detail->recipient_price;
            //13.健康捐
            $recordStr[] = '';
            //14.稅率別
            $recordStr[] = '1';
            //15.紅利點數折扣金額
            $recordStr[] = '';
            //16.明細備註
            $recordStr[] = '';

            $detailInvoices[$i] = implode('|', $recordStr);

            //換行
            $detailInvoices[$i] .= "\r\n";

            $recordStr = null;
            $i++;
        }

        return $detailInvoices;
    }

    /**
     * 取得發票作廢或折讓
     * @param $orderDate
     * @return string
     */
    public function getStatusIsInvalidOrDebit($orderDate)
    {
        $now = Carbon::now();
        $dt = Carbon::parse($orderDate);
        return ($now->diffInMonths($dt)) ? 3 : 2;
    }

    /**
     * 取付款方式
     * @param $key
     * @return string
     */
    private function getPaymentMethod($method)
    {
        switch($method) {
            case '111':
                $paymentMethod = '信用卡';
                break;
            case '211':
                $paymentMethod = 'ATM虛擬帳號';
                break;
            case '411':
                $paymentMethod = 'iPassPay電子餘額支付';
                break;
            case '611':
                $paymentMethod = 'LINEPay';
                break;
            default:
                $paymentMethod = '未定義的付款方式';
                break;
        }

        return $paymentMethod;
    }
}
