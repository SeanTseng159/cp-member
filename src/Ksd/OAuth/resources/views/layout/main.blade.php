<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <title>CityPass都會通 - 會員登入</title>

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

        <style>
            body {
                background-color: #f7f7f7;
            }

            a {
                text-decoration: none;
            }

            a:hover {
                text-decoration: none;
            }

            img {
                width: auto;
                max-width: 100%;
                height: auto;
            }

            ul {
                list-style-type: none;
                padding: 0;
            }

            nav .top {
                float: left;
                width: 100%;
                height: 30px;
                background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAfCAIAAAFZnemlAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDE0IDc5LjE1Njc5NywgMjAxNC8wOC8yMC0wOTo1MzowMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2NWViNDE0NC1mMDllLWVkNGMtODYxYi04MWVhZDMwNjFiMjkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MzE1QkQ2RDg4RjdDMTFFNTk3MTZGMUE0OUE3RkZCQ0IiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MzE1QkQ2RDc4RjdDMTFFNTk3MTZGMUE0OUE3RkZCQ0IiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NjE0Mjk0ZWMtMmQyNC1jMDQ2LTg4MzQtNmFiMDkzMjkwYWI0IiBzdFJlZjpkb2N1bWVudElEPSJhZG9iZTpkb2NpZDpwaG90b3Nob3A6NTVlY2VlMDMtOTRkYi0xMWU1LWE3ZDUtZmVhMjhmNDkwZTQwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+9OJB6wAAAONJREFUeNpifPfuHQMqYGLAAMQJAQQQ4////6llFkAAMQDNevPmDX4Smz6iDCdPEUAAgdz08eNH/CTD+/fvgSz85PA2CSCAoOngw4cP//79ExISIpvNArTx79+/ECFK2KMuGoouAgggUIqEgAULFlDChho0e/ZsuCh5bJBBc+fOhYuSzWaYP38+XJQS9mgYjYbRaBjRJ4wAAozpPyoA1nYLFy6E1HkDIs6ALDRnzpzXr19jKqWnOMhBwEpg3rx5kDYaMhgQcVDcA1u4mEoHSpxhNA2NpqHRNDSahkbT0GgaIk0cAD6jVz76RFQyAAAAAElFTkSuQmCC');
            }

            nav .bottom {
                float: left;
                width: 100%;
                background-color: white;
                padding: 12px 15px;
                text-align: center;
            }

            nav .bottom img {
                width: 220px;
                max-width: 100%;
                height: auto;
            }

            .form-control {
                border-radius: 2px;
                height: 42px;
            }

            .warpper {
                width: 400px;
                max-width: 100%;
                margin: 0 auto;
                margin-top: 50px;
                margin-bottom: 50px;
            }

            .warpper-2 {
                width: 600px;
                max-width: 100%;
                margin: 0 auto;
                margin-top: 30px;
                margin-bottom: 50px;
            }

            .form-login {
                width: 100%;
            }

            .btn-login {
                color: white;
                background-color: #ff8209;
                padding: 8px 12px;
                border-color: #ff8209;
                margin-top: 30px;
                font-size: 16px;
                border-radius: 2px;
            }

            .btn-login:hover {
                color: white;
            }

            .box-warp {
                text-align: center;
                color: #aaa;
                font-size: 16px;
                font-weight: 300;
                margin-bottom: 60px;
            }

            .box-warp .col-half {
                float: left;
                width: 50%;
                margin-top: 35px;
            }

            .box-warp .col-half:first-child {
                border-right: 1px solid #aaa;
            }

            .box-warp .col-half:last-child {
                border-left: 1px solid #aaa;
            }

            .box-warp a {
                color: #aaa;
            }

            .box-warp a:hover {
                color: #aaa;
            }

            .btn-link {
                padding: 6px 0;
                color: #009ce1;
                font-size: 16px;
                border-bottom: 1px solid #009ce1;
            }

            .btn-link:hover {
                color: #009ce1;
                border-bottom: 1px solid #009ce1;
                text-decoration: none;
            }

            .img-warp {
                margin-bottom: 35px;
            }

            .img-warp .col-half {
                float: left;
                width: 50%;
                padding: 15px;
            }

            .panel {
                background-color: transparent;
                border: 0;
                box-shadow: none;
            }

            .panel .panel-heading {
                text-align: center;
                color: #ff8209;
                border-color: #ff8209;
                font-size: 22px;
                background-color: transparent;
                padding-bottom: 20px;
            }

            .panel .panel-body {
                padding: 30px 0 0;
            }

            .panel .panel-footer {
                border-color: #ff8209;
                background-color: transparent;
            }

            .panel .panel-footer .checkbox {
                font-size: 16px;
            }

            .panel .panel-footer a {
                color: #ff8209;
            }

            ul,
            ul li,
            ul li > div {
                float: left;
                width: 100%;
            }

            ul li {
                margin-bottom: 30px;
            }

            ul li .title {
                margin-bottom: 10px;
                font-size: 18px;
            }

            ul li .icon {
                margin-right: 10px;
                font-size: 24px;
            }

            ul li .desc {
                color: #aaa;
                font-size: 15px;
                font-weight: 300;
            }

            .btn-warp {
                color: #aaa;
                font-size: 16px;
                font-weight: 300;
                margin-bottom: 60px;
            }

            .btn-warp .col-half {
                float: left;
                width: 50%;
                margin-top: 35px;
            }

            .btn-warp .col-half:first-child {
                padding-right: 15px;
                text-align: right;
            }

            .btn-warp .col-half:last-child {
                padding-left: 15px;
                text-align: left;
            }

            .btn-warp .col-half .cancal {
                width: 200px;
                padding: 14px 12px;
                border-radius: 2px;
                color: white;
                background-color: #ff8209;
            }

            .btn-warp .col-half .agree {
                width: 200px;
                padding: 14px 12px;
                border-radius: 2px;
                color: white;
                background-color: #eb5405;
            }

            @media only screen and (max-width: 560px) {
                .img-warp .col-half {
                    padding: 5px;
                }

                .panel .panel-heading {
                    font-size: 20px;
                    padding: 10px 5px 15px;
                }

                .btn-warp .col-half {
                    padding: 0 15px;
                }

                .btn-warp .col-half .cancal,
                .btn-warp .col-half .agree {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
