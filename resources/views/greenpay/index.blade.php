
<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        
        {{--  var backend_data = {!! $data !!};  --}}
        
        <form id="form" action="{{ $paymentUrl }}" method="POST" class="hidden">
            <input type="hidden" name="orderNo" value="{{ $orderNo }}" />
            <input type="hidden" name="amount" value="{{ $amount }}" />
            <input type="hidden" name="source" value="{{ $source }}" />
            <input type="hidden" name="platform" value="{{ $platform }}" />
            <input type="hidden" name="sucessUrl" value="{{ $sucessUrl }}" />
            <input type="hidden" name="faileUrl" value="{{ $faileUrl }}" />
            <input type="hidden" name="callbackUrl" value="{{ $callbackUrl }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
