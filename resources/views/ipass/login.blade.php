<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>愛Pass 會員登入</title>
    </head>
    <body>
        <form id="form" action="{{ env('IPASS_OAUTH_PATH') . 'auth/token' }}" method="POST" class="hidden">
            <input type="hidden" name="response_type" value="{{ $response_type }}" />
            <input type="hidden" name="client_id" value="{{ $uid }}" />
            <input type="hidden" name="code" value="{{ $code }}" />
            <input type="hidden" name="redirect_url" value="{{ $redirect_url }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
