<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>调试</title>
        <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">

    </head>
    <body>
    <div class="list-group">
        @foreach ($list as $item)
            <div  class="list-group-item">
                <h4 class="list-group-item-heading">{{ $item['time'] }}</h4>
                <h5 class="list-group-item-text">sql语句: {{ $item['data']['sql'] }}</h5>
                <h5 class="list-group-item-text">耗费: {{ $item['data']['time'] }} 毫秒</h5>
            </div>

        @endforeach
    </div>
    </body>
</html>
