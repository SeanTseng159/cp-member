<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 600px; max-width: calc(100% - 20px); font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 20px;font-weight: bold;color: #666;margin-top: 5px;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;">訂單繳費完成通知</h2>
            <table width="100%" border="0" cellspacing="0" cellpadding="8">
                <tr>
                    <td>親愛的顧客，您好:</td>
                </tr>
                <tr>
                    <td>訂單編號 <b>{{ $orderNo }}</b> 已經繳費成功。 請您放心謝謝！</td>
                </tr>
                <tr>
                    <td>若為ATM虛擬帳號付款，請等待15~20分鐘，待系統和銀行端核對金額後，訂單狀態會自動更新。</td>
                </tr>
                <tr>
                    <td>每日凌晨0點~3點00分因銀行系統維護，此段期間付款之訂單，請於凌晨6點過後再進行訂單查詢。</td>
                </tr>
                <tr>
                    <td>基於資料安全，在此不再顯示訂單明細，您可登入<a href="{{ $url }}" target="_blank">我的訂單</a>，查詢訂單內容或付款相關資訊。</td>
                </tr>
            </table>

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
