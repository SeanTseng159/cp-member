<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <title>CityPass城市通 - 應用程式授權</title>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <style>
            .form-login {
                width: 500px;
                margin: 0 auto;
                margin-top: 10%;
            }

            .img {
                margin: 20px 0 40px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form class="form-login" action="{{ url('oauth/member/authorize') }}" method="POST">
                <div class="panel panel-default">
                  <div class="panel-heading"><b>{{ $auth_client->name }}</b> 要求授權</div>
                  <div class="panel-body">
                    <div class="text-center">
                        <img class="img" src="https://www.ipasskhcc.tw/assets/img/logo-white.png" class="img-rounded">
                    </div>
                    <p>將被取用項目:</p>
                    <ul>
                        <li>會員資料(email、姓名...)</li>
                    </ul>
                    <input type="hidden" name="auth_client_id" value="{{ $auth_client->id }}">
                    <input type="hidden" id="revoked" name="revoked" value="0">
                  </div>
                  <div class="panel-footer ">
                    <button type="submit" class="btn btn-success btn-default">確認</button>
                    <button type="submit" class="btn btn-default" id="cancal">取消</button>
                  </div>
                </div>
            </form>
        </div>

        <script>
            document.getElementById('cancal').addEventListener("click", function () {
                document.getElementById('revoked').setAttribute('value', '1');
            });
        </script>
    </body>
</html>
