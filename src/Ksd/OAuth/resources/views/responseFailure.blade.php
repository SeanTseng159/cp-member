<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <form id="form" action="{{ session('redirect_url') }}" method="POST" class="hidden">
            <input type="hidden" name="code" value="{{ $errorCode }}" />
            <input type="hidden" name="message" value="{{ $msg }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
