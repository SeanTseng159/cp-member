<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <form id="form" action="{{ env('IPASS_PAY_API_PATH') . 'third_party/Pay' }}" method="POST" class="hidden">
            <input type="hidden" name="client_id" value="{{ $parameter->client_id }}" />
            <input type="hidden" name="order_id" value="{{ $parameter->order_id }}" />
            <input type="hidden" name="token" value="{{ $parameter->token }}" />
            <input type="hidden" name="timestamp" value="{{ $parameter->timestamp }}" />
            <input type="hidden" name="signature" value="{{ $parameter->signature }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
