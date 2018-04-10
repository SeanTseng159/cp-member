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
                            出貨後開立電子發票，將直接寄送至您的信箱。
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
                    <tr>
                        <td colspan="2" style="font-size: 14px;">
                            若付款方式選擇【ATM虛擬帳號】，繳款帳號與期限，請於CityPass都會通 訂單專區中查看。
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="font-size: 14px;">
                            付款方式選擇【一卡通付 iPASSpay】，則須繳費的資訊與狀態，請於一卡通付 iPASSpay APP上查詢。
                        </td>
                    </tr>
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
