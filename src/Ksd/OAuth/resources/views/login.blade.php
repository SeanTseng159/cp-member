@extends('oauth::layout.main')

@include('oauth::layout.header')

@section('content')
<div class="warpper">
    <form class="form-login" action="{{ url('oauth/member/login?platform=' . $platform) }}" method="POST">
        <div class="form-group">
            <input type="email" class="form-control" id="email" name="email" placeholder="帳號 ( Email信箱 )">
        </div>
        <div class="form-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="密碼">
        </div>
        <input type="hidden" name="auth_client_id" value="{{ $auth_client_id }}">
        <button type="submit" class="btn btn-block btn-login">登入</button>
    </form>
    <div class="row box-warp">
        <div class="col-half">
            <a href="{{ $web_url . '/forgetPassword' }}" target="_blank">忘記密碼</a>
        </div>
        <div class="col-half">
            <a href="{{ $web_url . '/create' }}" target="_blank">註冊加入會員</a>
        </div>
    </div>
    <div class="row text-center">
        <div class="col-xs-12">
            <a id="cancal" class="btn btn-link" data-href="{{ session('cancel_url') }}" data-orghref="{{ env('IPASS_WEB_PATH') }}">取消登入</a>
        </div>
    </div>
</div>
<script>
document.getElementById('cancal').addEventListener("click", function () {
    var href = this.dataset.href;
    var orgHref = this.dataset.orghref;
    location.href = (href) ? href : orgHref;
});
</script>
@endsection
