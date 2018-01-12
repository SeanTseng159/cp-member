@extends('oauth::layout.main')

@section('content')
<div class="warpper-2">
    <div class="row img-warp">
        <div class="col-half">
            <img src="{{ asset('img/ipasslogo.png') }}">
        </div>
        <div class="col-half">
            <img src="{{ asset('img/citypasslogo.png') }}">
        </div>
    </div>
    <form id="form" class="form-login" action="{{ url('oauth/member/authorize?platform=' . $platform) }}" method="POST">
        <div class="panel panel-default">
            <div class="panel-heading">『{{ $auth_client->name }} 』 想要求以下權限：</div>
            <div class="panel-body">
                <ul>
                    <li>
                        <div class="title">
                            <span class="icon"><i class="fa fa-user" aria-hidden="true"></i></span> 將收到您的基本個人資料
                        </div>
                        <div class="desc">
                            包含帳號、姓名、個人檔案、手機號碼，等相關資訊等相關資訊。
                        </div>
                    </li>
                    <li>
                        <div class="title">
                            <span class="icon"><i class="fa fa-envelope-o" aria-hidden="true"></i></span> 允許發送電子郵件
                        </div>
                        <div class="desc">
                            『 {{ $auth_client->name }} 』可以直接寄送電子郵寄至您的信箱。
                        </div>
                    </li>
                </ul>
                <input type="hidden" name="auth_client_id" value="{{ $auth_client->id }}">
                <input type="hidden" id="revoked" name="revoked" value="0">
            </div>
            <div class="panel-footer">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="input_agree"> 同意 {{ $auth_client->name }}「<a href="https://www.ipasskhcc.tw/home/privacy" target="_blank">服務條款與隱私權政策</a>」
                    </label>
                </div>
            </div>
        </div>
    </form>
    <div class="row btn-warp">
        <div class="col-half">
            <button class="btn cancal" id="cancal">拒絕</button>
        </div>
        <div class="col-half">
            <button class="btn agree" id="agree">允許</button>
        </div>
    </div>
</div>
<script>
document.getElementById('cancal').addEventListener("click", function () {
    alert('拒絕授權，登入失敗!');
    document.getElementById('revoked').setAttribute('value', '1');
    document.getElementById('form').submit();
});

document.getElementById('agree').addEventListener("click", function () {
    var checked = document.getElementById('input_agree').checked;

    if (checked) {
        document.getElementById('form').submit();
    }
    else {
        alert('請同意「服務條款與隱私權政策」');
    }

    return false;
});
</script>
@endsection
