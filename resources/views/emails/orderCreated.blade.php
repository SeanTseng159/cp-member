<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 600px; max-width: calc(100% - 20px); font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 20px;font-weight: bold;color: #666;margin-top: 5px;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;">訂單成立通知</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td>親愛的顧客，您好:</td>
                </tr>
                <tr>
                    <td>已收到您於CityPass都會通 的訂購資訊，感謝您的訂購。</td>
                </tr>
                <tr>
                    <td>您可隨時於本網站登入會員並連結至「我的訂單」查詢訂單相關資料。</td>
                </tr>
                <tr>
                    <td><b>提醒您：</b>我們不會以電話或簡訊通知改變付款方式。</td>
                </tr>
            </table>


            <div style="width: 100%; padding: 20px 0;"></div>

            <div style="width: 600px; max-width: 100%;">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>
                            <b>訂單編號</b>
                        </td>
                        <td>
                            {{ $order->orderNo }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>訂購時間</b>
                        </td>
                        <td>
                            {{ $order->orderDate }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>訂購明細</b>
                        </td>
                        <td>
                            請至 <a href="{{ $url }}" target="_blank">訂單查詢網址</a> 查詢
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>發票說明</b>
                        </td>
                        <td>
                            出貨後開立電子發票(交通類除外)，將直接寄送至您的信箱。
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>出貨進度</b>
                        </td>
                        <td>
                            您可隨時至 <a href="{{ $url }}" target="_blank">訂單查詢網址</a> 查詢出貨進度
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 14px;">
                            本通知函只是通知您本系統已經收到您的訂購訊息、並供您再次自行核對之用，不代表交易已經確認/完成。
                        </td>
                    </tr>
                    @if($order->payment['method'] == "atm")
                    <tr>
                        <td colspan="2" style="font-size: 14px;">
                            為保留訂購權利，若未繳費，請儘速繳費，ATM繳費資訊如下。
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>繳費銀行</b>
                        </td>
                        <td>
                            {{ $order->payment['bankName']}}（{{ $order->payment['bankId']}}）
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>繳費帳號</b>
                        </td>
                        <td>
                            {{ $order->payment['virtualAccount'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>繳費期限</b>
                        </td>
                        <td>
                            {{ $order->payment['paymentPeriod'] }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <div style="width: 100%; padding: 20px 0;"></div>

            <div style="width: 600px; max-width: 100%;">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>
                                <span style="color:#DA4B3D;">※ 此信件為系統發出，請勿直接回覆!<br>如果有任何疑問或建議事項，歡迎隨時寄信至 service@citypass.tw，我們將竭誠為您服務，感謝您的配合。謝謝！</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
