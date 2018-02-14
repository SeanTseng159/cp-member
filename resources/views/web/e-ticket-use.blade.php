@extends('layouts.main')

@section('content')
<div class="tabs-content">
    <div class="terms-content styel2 margin-top-30">
        <ul class="disc-list padding-left-15 ul-li-15">
            <li>參與活動或入場前，請出示APP內的電子票劵，服務人員會以手持式驗票機掃描 QR Code後確認入場。</li>
            <li>若為電子兌換券，請出示 QR Code讓服務人員掃描驗證，換取入場憑證或商品。</li>
            <li>每張電子票券，僅只適用於已獲確認的日期及時間。</li>
            <li>電子票券將顯示有效的使用日期時間，請依規定時間內使用完畢。</li>
            <li>每張票券會標示使用狀態，包含未使用、已使用、已失效與已轉贈的票券將分區顯示，請點擊票券列表，進入詳細頁，瀏覽更多資訊。</li>
            <li>進入票券詳細頁後，也可左右切換觀看其他票券資訊。</li>
            <li>【未使用】為票券未經使用，票券列表預設顯示此狀態。</li>
            <li>
                <p>【已使用】為票券已驗證或已開啟使用，若使用效期有區間，例如一日券或二日券等，該張票券一開啟使用後，便歸屬於已使用的狀態。</p>
                <ul class="decimal-list padding-left-30">
                    <li>使用紀錄：已核銷驗證票券之使用歷程記錄。</li>
                </ul>
                <div class="clearfix"></div>
            </li>
            <li>【已失效】為票券已超過使用效期，逾期未使用之票券，可自行於票匣中刪除。</li>
            <li>
                <p>【已轉贈】為票券已轉贈於他人之狀態，僅限票券狀態為未使用方可作轉贈。</p>
                <ul class="decimal-list padding-left-30">
                    <li>被贈票者的手機號碼需與註冊CityPass都會通為相同號碼。</li>
                    <li>轉贈成功後，原持有者之票券狀態將會改顯示_已轉贈，並顯示轉贈對象資料。</li>
                    <li>一個 QRCode 僅能提供一人入場，請勿將QRCode 截圖分享或公開給多人，以避免發生無法驗證入場的狀況。</li>
                </ul>
                <div class="clearfix"></div>
            </li>
            <li>主題類型：預設全部，會依據商品屬性的不同做區分，使用者可點擊個分類快速找到需要瀏覽的票券。</li>
            <li><i class="glyphicon glyphicon-bell color-orange bell"></i>今日票券：提醒使用者該張票券可於今日作使用。</li>

        </ul>
    </div>
</div>
@endsection
