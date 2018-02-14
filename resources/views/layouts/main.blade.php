<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <title>CityPass都會通</title>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

        <style>
            html {
                font-size: 100%;
            }

            body {
                background-color: #fff;
                font-family: '微軟正黑體', 'Microsoft JhengHei', 'Helvetica Neue', 'Helvetica', sans-serif;
                padding-bottom: 30px;
            }

            .main-title2 {
              float: left;
              width: 100%;
              text-align: center;
              color: #333;
              letter-spacing: 1px;
              padding-top: 20px;
              padding-bottom: 15px;
              font-size: 1.875rem;
            }

            .margin-top-30 {
            	margin-top: 30px;
            }

            .color-blue {
                color: #009ce1;
            }

            .color-red {
                color: #f44336;
            }

            .color-orange {
                color: #ff8209;
            }

            .btn-link {
                color: #ff0101;
                text-decoration: underline;
            }

            .btn-link:hover {
                color: #ff0101;
            }

            ul {
                list-style-type: none;
                padding: 0;
                margin: 0;
            }

            ul.decimal-list {
              float: left;
              width: 100%;
              list-style-type: decimal;
            }

            ul.circle-list {
              float: left;
              width: 100%;
              list-style-type: circle;
            }

            ul.disc-list {
              float: left;
              width: 100%;
              list-style-type: disc;
            }

            ul.lower-latin-list {
              float: left;
              width: 100%;
              list-style-type: lower-latin;
            }

            ul.circle-list>li,
            ul.lower-latin-list>li,
            ul.lower-latin-list>li {
                line-height: 28px;
                font-size: .875rem;
            }

            .tabs-content {
                float: left;
                width: 100%;
            }

            .nav-warp {
                width: 100%;
                display: block;
                overflow: hidden;
            }

            .scroll-warp {
                display: block;
                width: 100%;
                text-align: center;
                overflow: auto;
                border-bottom: 1px solid #fd8325;
            }

            .nav-tabs {
                display: inline-block;
                border-bottom: 0;
                margin-bottom: -5px;
            }

            .nav-tabs>li {
                margin-bottom: 0;
            }

            .nav-tabs>li>a {
                text-align: center;
                font-size: 14px;
                color: #8391a5;
                width: auto;
                height: 42px;
                font-weight: 300;
                margin-right: 0 !important;
                border: 1px solid #d1dbe5;
                border-bottom: 0;
                border-right: 0;
                margin-right: 3px;
                border-radius: 0;
            }

            .nav-tabs>li:last-child>a {
                border-right: 1px solid #d1dbe5;
            }

            .nav-tabs>li.active>a, .nav-tabs>li.active>a:focus, .nav-tabs>li.active>a:hover {
                border-right: 0;
                border-bottom: 0;
                color: #fff;
                background-color: #fd8325;
            }

            .nav-tabs>li>a:hover {
                color: #fff;
                background-color: #fd8325;
            }

            .tab-content {
                border: 1px solid #d1dbe5;
                border-top: 0;
            }

            // 服務條款
            .terms-content {
              float: left;
                width: 100%;
              padding: 30px;
            }

            .terms-content.styel2 {
                padding: 0 15px 30px;
            }


            .terms-content .title {
                float: left;
                width: 100%;
                color: #ff8209;
                font-weight: 600;
                font-size: 1rem;
            }

            .terms-content .title.styel2 {
                margin: 30px 0 10px;
            }

            .terms-content .content {
                float: left;
                width: 100%;
                padding: 15px;
            }

            .terms-content .padding-left-15 {
                padding-left: 15px;
            }

             .terms-content .padding-left-30 {
                padding-left: 30px;
              }

            .terms-content p {
                margin-bottom: 5px;
              }

            .terms-content .top-text {
                margin-bottom: 15px;
              }

            .bell {
                color: #ff8209;
                border-radius: 50%;
                border: 1px solid #ff8209;
                padding: 3px 3.5px 3px 4px;
                text-align: center;
                font-size: 0.65rem;
                margin-right: 5px;
            }

            .ul-li-15 li {
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
        @yield('script')
    </body>
</html>
