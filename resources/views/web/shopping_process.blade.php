@extends('layouts.main')

@section('content')
<div class="main-title2">購買方式</div>

<div class="tabs-content">
    <div class="terms-content styel2">
        <div class="title styel2">購買方式</div>
        <ul class="decimal-list padding-left-15">
            <li class="color-blue">選購商品</li>
            <div>
                <p>· 挑選欲購買的商品，選擇規格及數量並加入購物車。</p>
            </div>
            <li class="color-blue">登入會員</li>
            <div>
                <p>· 輸入您的會員帳號、密碼登入。</p>
                <p>· 若尚未註冊CityPass都會通會員，請點擊下方「註冊」免費加入會員。如果您本身擁有愛PASS的會員帳號，亦可使用愛PASS網站的會員帳號作登入。</p>
            </div>
            <li class="color-blue">加入並檢視購物車商品</li>
            <div>
                <p>· 請確認您欲購買的商品、規格、數量與金額等資訊，若確認無誤後，請點擊「下一步」按鈕，進行付款、取貨方式選擇。</p>
                <p>· 如果購物車內的商品規格欲變動，請點擊「編輯」或「刪除」按鈕修改購買項目。</p>
            </div>
            <li class="color-blue">填寫訂購資訊</li>
            <div>
                <p>· 若您有優惠代碼，可於訂購時輸入優惠代碼進行優惠折扣。</p>
                <p>· 確認訂購人資訊，包含帳號、姓名、手機號碼等資料。</p>
                <p>· 選擇取貨方式</p>
                <p>· 選擇付款方式</p>
                <p>· 選擇發票類型</p>
                <p>· 輸入完成後，請點擊確認結帳按鈕</p>
            </div>
            <li class="color-blue">訂單完成</li>
            <div>
                <p>· 完成訂購後，隨後會透過系統將訂單完成通知信傳送至您的電子信箱。</p>
            </div>
        </ul>
        <div class="title styel2">付款方式</div>
        <p>目前提供付款方式：「信用卡一次付清」、「ATM虛擬帳號轉帳」、「LINEPay付款」。各項商品可用付款方式以商品說明與結帳頁標示為主。</p>
        <ul class="padding-left-15">
            <li>
                <p><span class="color-blue">「信用卡一次付清」</span>：</p>
                <p>目前本網站接受僅限台灣地區發行之VISA、Master Card、JCB信用卡，信用卡一次完成付清。</p>
                <p>· 本網站為提高線上金流交易安全性，信用卡將進行3D驗證交易，在您輸入卡號與授權碼後，會連結至輸入密碼的頁面，如您尚未取得密碼，請您至發卡行網站取得密碼。</p>
                <p>· 付款完成後，請點選「
                    <a class="btn-link">訂單專區</a>」查看商品資訊。</p>
                <p>· 如刷卡過程中出現空白畫面，請不要關閉視窗或重新整理網頁，以免重覆扣款或刷卡失敗。</p>
                <p>· 確實完成繳費動作後，本平台將處理傳送電子票券至APP票匣內，或準備商品出貨事宜。</p>
                <p><span class="color-blue">「ATM虛擬帳號轉帳」</span>：</p>
                <p>購買完成後，我們將提供該筆訂單的專用「轉帳帳號」，持此帳號至ATM自動櫃員機、網路銀行、銀行臨櫃選擇轉帳即可付款。部分銀行將向您收取轉帳手續費。</p>
                <p>· 請您最遲於購買的隔日23:30前轉入款項(繳款期限是不受例假日所影響)，逾期未收到款項，本訂單將自動取消。</p>
                <p>· 若您有二筆以上的訂單，請依各別的專用匯款帳號轉帳，無法合併付款。</p>
                <p>· 繳費後CityPass都會通將確認訂單並傳送繳費完成通知信。</p>
                <p>· 確實完成繳費動作後，本平台將處理傳送電子票券至APP票匣內，或準備商品出貨事宜。</p>
                <p><span class="color-blue">「LINE Pay」</span>：</p>
                <p>本網站提供LINE Pay的付款方式。</p>
                <p>· 請先於您的LINE APP中註冊您的信用卡相關資訊後，再選擇以LINE Pay方式付款。</p>
                <p>· 當您選擇以LINE Pay方式付款，畫面將會切換至LINE Pay的付款頁面，請您依照LINE Pay的頁面引導進行付款認證動作。</p>
                <p>· 若有LINE Pay相關問題例如註冊、條碼使用、LINE Points折抵失敗、使用紀錄釐清等)，您可至以下連結查詢<a class="btn-link" href="https://line.me/zh-hant/pay/howto">https://line.me/zh-hant/pay/howto</p>
            </li>
        </ul>
        <div class="title styel2">開立發票</div>
        <p>購物完成付款後，本網站將依選擇開立的發票方式提供，發票一經開立將無法變更。</p>
        <p>· 根據財政部令「<a class="btn-link" href="http://gazette.nat.gov.tw/EG_FileManager/eguploadpub/eg019159/ch04/type2/gov30/num8/Eg.htm">電子發票實施作業要點</a>」，於CityPass都會通 消費開立之「二聯式電子發票」、「三聯式電子發票」，不直接郵寄紙本發票，並以Email發送發票開立通知信給您，以茲證明。相關資料請參考<a class="btn-link" href="https://www.einvoice.nat.gov.tw/wSite/mp?mp=1">財政部電子發票整合服務平台</a>。</p>
        <p>· 二聯式電子發票：訂單成立完成結帳後，發票開立通知信將會寄到您的電子信箱，請至信箱內確認。</p>
        <p>· 三聯式電子發票：訂單成立完成結帳後，開立通知信與附件三聯發票證明聯與將會寄到您的電子信箱，可下載附件列印即可透過此證明聯向公司報帳。</p>
        <p>· 交通類型商品不開立發票，僅提供購買證明單。</p>
        <p>· 票券商品發票於付款後3天內完成Email發送；實體商品發票將於出貨日期+7日Email發送。如超過10天仍未收到，請至客服信箱留言，將有專人為您協助處理。</p>
    </div>
</div>
@endsection
