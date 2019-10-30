<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">客服追蹤通知信</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>親愛的{{ $name }}，您好:</td>
                    </tr>
                    <tr>
                        <td>感謝您使用CityPass預訂餐廳，您的訂位資訊如下：</td>
                    </tr>
                    <tr>
                        <td>{{ $shopname }}</td>
                    </tr>
                    <tr>
                        <td>訂位編號 {{ $number }}</td>
                    </tr>
                    <tr>
                        <td>
                           日期 :{{ $date }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                           時間 :{{ $time }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            人數 : {{ $people }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ※如不克前往，請貼心手動取消訂位。
                        </td>
                    </tr>
                    <tr>
                        <td>
                            查看或取消請點擊{{ $link }}
                        </td>
                    </tr>
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
