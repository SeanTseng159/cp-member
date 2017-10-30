<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <form id="form" action="{{ session('redirect_url') }}" method="POST" class="hidden">
            <input type="hidden" name="access_token" value="{{ $data->access_token }}" />
            <input type="hidden" name="expires_in" value="{{ $data->expires_in }}" />
            <input type="hidden" name="token_type" value="{{ $data->token_type }}" />
            <input type="hidden" name="member" value="{{ $data->member }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
