<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        {{--<meta name="viewport" content="width=device-width, initial-scale=1">--}}
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta name="format-detection" content="telephone=no"/>

        <title>出错了!</title>

        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                width: 100%;
                margin: 0;
                word-break:break-all;
                word-wrap: break-word;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 36px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .debug{
                background-color: #000;
                color: #fff;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    出错了!
                </div>

                <div>
                    错误信息: {{$error_msg}} <br/>
                </div>
            </div>
        </div>
        <div style="position: relative; top: -30%; padding: 0 1.5rem;">
            @if ($debug != '')
                <div style="text-align: left" class="debug">
                    <p>file:  {{$debug['file']}}</p>
                    <p>line:  {{$debug['line']}}</p>
                    <p>type:  {{$debug['type']}}</p>
                @foreach ($debug['trace'] as $line)
                        <p> {{ $line }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </body>
</html>
