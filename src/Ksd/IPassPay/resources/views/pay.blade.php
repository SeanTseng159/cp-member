<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <form id="form" action="http://172.104.86.21/api/ipass/pay" method="POST" class="hidden">
            <input type="hidden" name="client_id" value="{{ $order->client_id }}" />
            <input type="hidden" name="respond_type" value="{{ $order->respond_type }}" />
            <input type="hidden" name="version" value="{{ $order->version }}" />
            <input type="hidden" name="lang_type" value="{{ $order->lang_type }}" />
            <input type="hidden" name="order_id" value="{{ $order->order_id }}" />
            <input type="hidden" name="order_name" value="{{ $order->order_name }}" />
            <input type="hidden" name="amount" value="{{ $order->amount }}" />
            <input type="hidden" name="item_name" value="{{ $order->item_name }}" />
            <input type="hidden" name="success_url" value="{{ url('ipass/callback') }}" />
            <input type="hidden" name="failure_url" value="{{ url('ipass/callback') }}" />
            <input type="hidden" name="timestamp" value="{{ $order->timestamp }}" />
            <input type="hidden" name="signature" value="{{ $order->signature }}" />
        </form>

        <script>
            document.getElementById("form").submit();
        </script>
    </body>
</html>
