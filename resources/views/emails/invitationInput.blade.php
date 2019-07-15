<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">邀請碼獲得禮物通知</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>親愛的會員{{ $name }}您好:</td>
                    </tr>
                    <tr>
                        <td>CityPass的使命是帶給您更優質的吃喝玩樂享受， </td>
                    </tr>
                    <tr>
                        <td>您的加入是我們的榮幸，現在送給您一份好禮！</td>
                    </tr>
                    <tr>
                        <td>免費獲得：</td>
                    </tr>
                    <tr>
                        <td>{{ $giftName }}</td>
                    </tr>
                    <tr>
                        <td>※禮物說明詳見登入會員我的帳戶後「我的禮物」專區</td>
                    </tr>
                    <tr>
                        <td>※CityPass保留公告變更、修改或終止本活動之權利。</td>
                    </tr>
                    <tr>
                        <td>CityPass都會通 敬上</td>
                    </tr>
                    <tr>
                        <td>官方網站 https://citypass.tw</td>
                    </tr>
                    <tr>
                        <td>FB粉絲團 https://www.facebook.com/citypass520</td>
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
