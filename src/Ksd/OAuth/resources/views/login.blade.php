<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <title>會員登入</title>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <style>
            .form-login {
                width: 300px;
                margin: 0 auto;
                margin-top: 10%;
                padding: 15px;
                border: 1px solid #eee;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form class="form-login" action="{{ url('oauth/member/login') }}" method="POST">
              <div class="form-group">
                <label for="email">帳號</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="帳號 (Email信箱)">
              </div>
              <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="密碼">
              </div>
              <input type="hidden" name="auth_client_id" value="{{ $auth_client_id }}">
              <button type="submit" class="btn btn-block btn-default">登入</button>
            </form>
        </div>
    </body>
</html>
