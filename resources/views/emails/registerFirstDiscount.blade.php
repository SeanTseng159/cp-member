<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">註冊完成</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>親愛的會員{{ $name }}您好： </td>
                    </tr>
                    <tr>
                        <td>我們準備了限量優惠給新加入的您，期待藉由這次機會，購買心目中的美好商品！<br>立即動動手指，加滿你的購物車吧！</td>
                    </tr>
                    <tr>
                        <td>優惠代碼名稱：{{ $codeName }}<br>代碼：{{ $codeValue }}<br>兌換期限：至{{ $endTime }}止</td>
                    </tr>
                    <tr>
                        <td>※結帳時輸入即可折抵。<br>※每人限使用一次，不得與其它優惠併用。<br>※特殊商品恕不提供折扣。<br>※使用代碼折抵後，該訂單商品一經核銷使用後不得申請退款。<br>※CityPass保留公告變更、修改或終止本活動之權利。</td>
                    </tr>
                    <tr>
                        <td>CityPass都會通 敬上</td>
                    </tr>
                    <tr>
                        <td>官方網站 https://citypass.tw<br>FB粉絲團 https://www.facebook.com/citypass520</td>
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
