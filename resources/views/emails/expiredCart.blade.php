<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width: 750px;font-size: 15px;color: #666;border: 1px solid #eaeaea;padding: 10px;">
            <h2 style="font-size: 15px;font-weight: bold;color: #666;padding-bottom: 10px;border-bottom-width: 1px;border-bottom-style: dashed;border-bottom-color: #E6E6E6;margin-bottom: 15px;">商品移至收藏清單通知</h2>
            <div id="con">
                <table width="100%" border="0" cellspacing="0" cellpadding="8">
                    <tr>
                        <td>親愛的{{ $name }}，您好:</td>
                    </tr>
                    <tr>
                        <td>您於CityPass都會通 選擇加入購物車的商品，已經自購物車移除，並安排至收藏清單。</td>
                    </tr>
                    <tr>
                        <td>
                            CityPass都會通 還有更多推薦商品，等您回來重新選購加入!
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-weight: bold;">
                            商品內容
                        </td>
                    </tr>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <table style="background-color: #F2F2F2;width:100%;">
                                <tr>
                                    <td rowspan="3" style="padding-right: 5px;width:190px;">
                                        <table style='margin: 5px;'>
                                            <tr>
                                                <td style="width:182px;height:100px;background: url('{{$item->imageUrl}}') top left no-repeat;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <span style="font-weight: bold;text-decoration: underline;">{{$item->name}}</span>
                                    </td>
                                </tr>
                                <tr><td>{{$item->spec}}&nbsp;</td></tr>
                                <tr>
                                    <td>
                                        <span style='padding-left:5px;font-size: 16px;font-weight: bold;'>NT$ {{$item->price}}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>
                            <span>※ 電子郵件最多顯示10項商品，欲查看完整收藏清單商品請至網站/APP內查看。</span>
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
