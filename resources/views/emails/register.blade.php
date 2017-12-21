<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">註冊信箱確認</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>親愛的會員，您好:</td>
                    </tr>
                    <tr>
                        <td>感謝您註冊CityPass 城市通！為確認您的電子郵件地址，須做email帳號認證。</td>
                    </tr>
                    <tr>
                        <td>請點擊以下連結或貼至瀏覽器網址列，即可完成確認。</td>
                    </tr>
                    <tr>
                        <td>
                            <a style="margin: 30px 0 60px; padding: 6px 12px; border-radius: 4px; color: #fff; background-color: #fd8325; text-decoration: none;" href="{{ $link }}">點我開通信箱</a>
                        </td>
                    </tr>
                    <tr>
                        <td >
                            <span style="color:#DA4B3D;">※ 此信件為系統發出，請勿直接回覆，感謝您的配合。謝謝！</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
