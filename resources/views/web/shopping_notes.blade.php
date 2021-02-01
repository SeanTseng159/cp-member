@extends('layouts.main')

@section('content')
<div class="main-title2">購買須知</div>

<div class="tabs-content">
    <div class="scroll-warp">
        <ul id="nav" class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab_1" aria-controls="tab_1" role="tab" data-toggle="tab">購買方式</a></li>
            <li role="presentation"><a href="#tab_2" aria-controls="tab_2" role="tab" data-toggle="tab">取電子票方式</a></li>
            <li role="presentation"><a href="#tab_3" aria-controls="tab_3" role="tab" data-toggle="tab">取貨方式</a></li>
            <li role="presentation"><a href="#tab_4" aria-controls="tab_4" role="tab" data-toggle="tab">退換貨方式</a></li>
            <li role="presentation"><a href="#tab_5" aria-controls="tab_5" role="tab" data-toggle="tab">電子票轉贈</a></li>
        </ul>
    </div>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab_1">
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
                <p>目前提供付款方式：「信用卡一次付清」、「ATM虛擬帳號轉帳」、「iPASSPAY一卡通付」。各項商品可用付款方式以商品說明與結帳頁標示為主。</p>
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
                        <p>購買完成後，我們將提供該筆訂單的專用「轉帳帳號」，持此帳號至ATM自動櫃員機選擇轉帳即可付款。部分銀行將向您收取轉帳手續費。</p>
                        <p>· 請您最遲於購買的隔日23:30前轉入款項(繳款期限是不受例假日所影響)，逾期未收到款項，本訂單將自動取消。</p>
                        <p>· 若您有二筆以上的訂單，請依各別的專用匯款帳號轉帳，無法合併付款。</p>
                        <p>· 繳費後CityPass都會通將確認訂單並傳送繳費完成通知信。</p>
                        <p>· 確實完成繳費動作後，本平台將處理傳送電子票券至APP票匣內，或準備商品出貨事宜。</p>
                        <p><span class="color-blue">「iPASSPAY一卡通付」</span>：</p>
                        <p>本網站提供iPASSPay一卡通付的付款方式，點擊進入支付頁面。目前支付方式有兩種，「電子支付帳戶餘額」、「實體ATM」。</p>
                        <p>· 若選擇電子支付帳戶餘額，將扣除您帳戶內的餘額以支付訂單款項。</p>
                        <p>· 若選擇實體ATM轉帳，即產生虛擬帳號並請於指定日期前完成繳款，毋須支付再確認。繳款完成後可透過iPASSPay一卡通付APP確認付款交易成功狀態。</p>
                        <p>· 確實完成繳費動作後，本平台將處理傳送電子票券至APP票匣內，或準備商品出貨事宜。</p>
                    </li>
                </ul>
                <div class="title styel2">開立發票</div>
                <p>購物完成付款後，本網站將依選擇開立的發票方式提供，發票一經開立將無法變更。</p>
                <p>· 根據財政部令「<a class="btn-link" href="http://gazette.nat.gov.tw/EG_FileManager/eguploadpub/eg019159/ch04/type2/gov30/num8/Eg.htm">電子發票實施作業要點</a>」，於CityPass都會通 消費開立之「二聯式電子發票」、「三聯式電子發票」，不直接郵寄紙本發票，並以Email發送發票開立通知信給您，以茲證明。相關資料請參考<a class="btn-link" href="https://www.einvoice.nat.gov.tw/wSite/mp?mp=1">財政部電子發票整合服務平台</a>。</p>
                <p>· 二聯式電子發票：訂單成立完成結帳後，發票開立通知信將會寄到您的電子信箱，請至信箱內確認。</p>
                <p>· 三聯式電子發票：訂單成立完成結帳後，開立通知信與附件三聯發票證明聯與將會寄到您的電子信箱，可下載附件列印即可透過此證明聯向公司報帳。</p>
                <p>· 票券商品發票於付款後3天內完成Email發送；實體商品發票將於出貨日期+7日Email發送。如超過10天仍未收到，請至客服信箱留言，將有專人為您協助處理。</p>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab_2">
            <div class="terms-content styel2">
                <div class="title styel2">電子票券說明</div>
                <p>若為服務型商品，舉凡美食餐廳、旅遊活動體驗、交通行程、商品兌換等等，皆以電子票券QR code方式提供入場核銷。</p>
                <div class="title styel2">取票說明</div>
                <ul class="decimal-list padding-left-15">
                    <li>完成 CityPass都會通購買流程後，若選擇「電子票券(APP_我的票券)」取票，您將會在訂單專區或APP中我的票券內收到票券資訊以及入場QR code。</li>
                    <li>在餐廳或其他活動體驗現場，只要將您的電子票券提供給工作人員掃描即可進行報到入場，您可以使用以下方式：</li>
                    <p>· 若您使用的是 iOS 或 Android 手機，您可以下載 CityPass都會通APP，更能方便管理您的票券。只要將電子票券顯示給店家或工作人員掃描即可。</p>
                    <p>· 您也可以在智慧型手機上登入 CityPass都會通 至訂單頁面，並將電子票券顯示給報到工作人員掃描。</p>
                    <p>注意：此電子票券即您完成訂購後用以入場的憑據，請勿任意分享或截圖給他人以避免糾紛。驗證方式須遵循店家或主辦單位規定。</p>
                </ul>
                <div class="title styel2">APP_我的票券</div>
                <p>· 使用 CityPass都會通APP 時，可在主選單的「我的票券」列表中看見自己購買過的票券。</p>
                <p>· 在票券列表中會有icon顯示今日可使用的票券，點擊此票券進入詳細頁，便可瀏覽該活動商品的資訊與入場QR code。</p>
                <p>· 入場時只要將電子票券QR code顯示給店家或報到工作人員掃描即可。</p>
                <p>· 若超過4個小時仍未收到電子票券，請洽CityPass都會通客服人員。</p>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab_3">
            <div class="terms-content styel2">
                <div class="title styel2">服務說明</div>
                <p>若為CityPass都會通平台所購得的實體商品，商品皆為宅配到府。</p>
                <p>· 單筆訂單消費未滿999元，須加收運費80元。若有折扣免運活動則不在此規範內。</p>
                <p>· 我們將配送商品至您所指定的地點及指定的收貨人，請您務必保持手機暢通以確保商品能準時送達。</p>
                <p>· 目前配送範圍僅適用台灣本島，不包含外島與海外地區配送服務。</p>
                <p>· 宅配商品約5~7個工作天可到貨，訂製品、預購商品。</p>
                <p>· 若超過7天仍未收到商品，請洽CityPass都會通客服人員或發送郵件至service@citypass.tw，我們將有專人為您處理。</p>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab_4">
            <div class="terms-content styel2">
                <div class="title styel2">【電子票券】</div>
                <p class="color-blue">退換貨說明</p>
                <p>· 電子票券申請退票期限為各店家或主辦單位制訂，請先查看您所購買商品的「退貨須知」。</p>
                <p>· 為節省更換商品的等候時間，本平台全面採用只退不換的方式，若您有換貨需求，請辦理退貨後，重新訂購。</p>
                <p>· 如發現會員因個人因素退貨次數過多，CityPass都會通將依據服務條款，暫停或終止您全部或部份的購物與服務資格，敬請見諒。</p>
                <p class="color-blue">退貨申請</p>
                <p>商品辦理退訂/退貨，請點選《<a class="btn-link" href="https://middleware.citypass.tw/files/citypass_returnV2.docx">退貨申請</a>》填寫欲退貨品項、數量、點選退貨原因，包含訂單編號與電子信箱，並同意代為進行發票折讓後送出完成申請，客服人員於三天內將儘快與您聯繫確認。</p>
                <p class="color-blue">退貨方式</p>
                <p>若電子票券完成退貨流程，您的訂單狀態會顯示已退票，且CityPass都會通 APP票匣內將回收該張票券。</p>
                <p class="color-blue">退款方式</p>
                <p>退貨款項乃依據原訂單付款方式處理。</p>
                <p><span class="color-red">使用「信用卡」付款</span>：款項將退回原付款之信用卡。</p>
                <p>· 信用卡因受個人信用卡結帳週期影響，款項可能會顯示下期信用卡帳單上，請留意近二期信用卡帳單或向發卡銀行洽詢。</p>
                <p><span class="color-red">使用「ATM虛擬帳號轉帳」付款</span>：款項將退回您指定的銀行帳戶。</p>
                <p><span class="color-red">使用「iPASSPAY一卡通付」付款</span>：不論您使用「電子支付帳戶餘額」或「實體ATM」付款，退款皆會匯入您的「電子支付帳戶」內。</p>
                <p class="color-blue">退貨退款進度</p>
                <p>處理退貨申請約3~5個工作天，確認退貨屬實後，將退款流程為您辦理款項之退還。</p>
                <p>退款完成時我們將發通知信提醒您。</p>
                <div class="title styel2">【實體商品】</div>
                <p class="color-blue">退換貨說明</p>
                <p>· 依據消費者保護法之規定，消費者得於收到商品或接受服務後七天內，以退回商品或書面通知方式解除契約，無須說明理由及負擔任何費用或對價。但以下情形例外不適用：</p>
                <ul class="decimal-list padding-left-15">
                    <li>易於腐敗、保存期限較短或解約時即將逾期。</li>
                    <li>依消費者要求所為之客製化給付。</li>
                    <li>報紙、期刊或雜誌。</li>
                    <li>經消費者拆封之影音商品或電腦軟體。</li>
                    <li>非以有形媒介提供之數位內容或一經提供即為完成之線上服務，經消費者事先同意始提供。</li>
                    <li>已拆封之個人衛生用品。</li>
                    <li>國際航空客運服務商品申請退換貨需保持商品本體、保證書、配件(贈品)、所有隨附文件與原廠包裝的完整性，如有缺漏或毀損皆會影響自身退貨的權益，或需依照商品損毀程度進行費用的索取。</li>
                </ul>
                <p>· 單筆訂單僅受理一次來回退貨服務。</p>
                <p>· 若退貨後不符合贈品活動，則贈品請一併退回才可受理退貨服務。</p>
                <p>· 為節省更換商品的等候時間，本平台全面採用只退不換的方式，若您有換貨需求，請辦理退貨後，重新訂購。</p>
                <p>· 如發現會員因個人因素退貨次數過多，CityPass都會通將依據服務條款，暫停或終止您全部或部份的購物與服務資格，敬請見諒。</p>
                <p class="color-blue">退貨申請</p>
                <p>· 商品辦理退訂/退貨，請點選《<a class="btn-link" href="https://middleware.citypass.tw/files/citypass_returnV2.docx">退貨申請</a>》填寫欲退貨品項、數量、點選退貨原因，包含訂單編號與電子信箱，並同意代為進行發票折讓後送出完成申請，客服人員於三天內將儘快與您聯繫確認。</p>
                <p>· 申請退貨翌日起算15個工作天，若因無法聯繫、商品未寄回無法完成退貨作業，將自動取消退貨申請，且不可再次申請。</p>
                <p>· 退貨取件完成後，請保留宅配取貨單據，以方便日後查詢。</p>
                <p class="color-blue">退貨方式</p>
                <p>宅配收件：辦理退貨時，需由宅配到府收取包裏。申請退貨手續完成後，請保持手機暢通，避免因宅配人員無法與您連繫，而延誤退貨申請時間。</p>
                <p>· 請於退貨商品外包裝附上：姓名、電話、訂單編號、地址，以利退貨作業查詢。</p>
                <p class="color-blue">退款方式</p>
                <p>退貨款項乃依據原訂單付款方式處理。</p>
                <p><span class="color-red">使用「信用卡」付款</span>：款項將退回原付款之信用卡。</p>
                <p>· 信用卡因受個人信用卡結帳週期影響，款項可能會顯示下期信用卡帳單上，請留意近二期信用卡帳單或向發卡銀行洽詢。</p>
                <p><span class="color-red">使用「ATM虛擬帳號轉帳」付款</span>：款項將退回您指定的銀行帳戶。</p>
                <p><span class="color-red">使用「iPASSPAY一卡通付」付款</span>：不論您使用「電子支付帳戶餘額」或「實體ATM」付款，退款皆會匯入您的「電子支付帳戶」內。</p>
                <p class="color-blue">退貨退款進度</p>
                <p>收到退貨商品後，退貨處理作業約3~5個工作天，確認退貨商品無誤後，將於三週內辦理退款。退款完成將發通知信提醒您。</p>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab_5">
            <div class="terms-content styel2">
                <div class="title styel2">服務說明</div>
                <p>CityPass都會通APP票匣內的票券，可轉贈給其他CityPass都會通會員。 完成繳費後即可於APP票匣內收到票券，且對方需已下載CityPass都會通APP並且成為會員。操作步驟如下:
                </p>
                <p>步驟1: 開啟CityPass都會通 APP，點選【我的票券】，選擇您要轉贈的票券。</p>
                <p>步驟2: 點選【轉贈】，並輸入您要贈送對象的手機門號。再次確認被贈者的資料，即可轉贈成功。(該手機號碼需與註冊CityPass都會通為相同號碼。)</p>
                <p>步驟3: 被贈票者請開啟APP並登入會員，點選【我的票券】，即可找到該張票券。</p>
                <br>
                <p>提醒您：</p>
                <p>· 被贈票者的手機號碼需與註冊CityPass都會通為相同號碼。</p>
                <p>· 轉贈成功後，原持有者之票券狀態將會改顯示_已轉贈，並顯示轉贈對象資料。</p>
                <p>· 一個 QRCode 僅能提供一人入場，請勿將QRCode 截圖分享或公開給多人，以避免發生無法驗證入場的狀況。</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function() {
        setNavWidth();

        $(window).resize(function(){
            setNavWidth();
        });
    });

function setNavWidth() {
    if ($(window).width() < 600) {
        $('#nav').width(500);
    }
}
</script>
@endsection
