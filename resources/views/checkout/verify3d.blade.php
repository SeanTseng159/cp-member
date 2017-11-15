<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>3D Verify</title>
    </head>
    <body>
        <form id="verify_form" ref="card3dVerify" action="https://card3d.ubot.com.tw/MpiSite/Index.do" method="POST" class="hidden">
            <input type="hidden" name="merchantID" value="000100312300064" />
            <input type="hidden" name="terminalID" value="47001003" />
            <input type="hidden" name="AcquirerBIN" value="412948" />
            <input type="hidden" name="cardNumber" value="{{ $cardNumber }}" />
            <input type="hidden" name="expYear" value="{{ $expYear }}" />
            <input type="hidden" name="expMonth" value="{{ $expMonth }}" />
            <input type="hidden" name="totalAmount" value="{{ $totalAmount }}" />
            <input type="hidden" name="XID" value="{{ $orderNo }}" />
            <input type="hidden" name="RetUrl" value="{{ $RetUrl }}" />
            <input type="hidden" name="hyAction" value="index" />
        </form>

        <script>
            document.getElementById("verify_form").submit();
        </script>
    </body>
</html>
