<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">【重設密碼信】重設密碼連結30分鐘有效</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>{{ $name }}，您好:</td>
                    </tr>
                    <tr>
                        <td>您要求重設密碼。請前往這個連結輸入您的新密碼。</td>
                    </tr>
                    <tr>
                        <td>提醒你： 請在30分鐘內完成所有操作。</td>
                    </tr>
                    <tr>
                        <td>
                            <a style="margin: 30px 0 60px; padding: 6px 12px; border-radius: 4px; color: #fff; background-color: #fd8325; text-decoration: none;" href="{{ $link }}">點我重設密碼</a>
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
