<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style type="text/css">
        .page {
            max-width: 600px;
            width: 600px;
            background: #f0f0f0;
            margin: auto;
        }

        .header {
            background: #134f5c;
            text-align: center;
            padding: 8px
        }

        .logo {
            margin: auto;
            width: 80px;
        }

        .brand-name {
            color: #fff;
        }

        .content {
            padding: 8px;
            color: #202020;
        }

        .button {
            background-color: #275c87;
            color: #fff;
            text-decoration: none;
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0px 1px 5px 0px rgba(0,0,0,0.2), 0px 2px 2px 0px rgba(0,0,0,0.14), 0px 3px 1px -2px rgba(0,0,0,0.12);
        }

        table {
            width: 100%;
            border: 1px solid #e0e0e0;
        }

        table tr th {
            background: #404040;
            color: #fff;
            font-size: 12px;
            padding: 4px;
        }

        table tr:hover{
            background: #fff;
        }

        table tr td {
            font-size: 10px;
            border: 1px solid #e0e0e0;
        }

        .footer {
            margin-top: 32px;
            border-top: 1px solid #909090;
            color: #909090;
        }
    </style>
</head>
<body>
    <div id="app">

        <main class="page">
            <div class="header">
                <img class="logo" src="{{ url('/images/logo-transparent.png') }}" alt="Sunshine Logo" />
                <div class="brand-name" >Sunshine Healthcare - OneForce CRM</div>
            </div>
            <div class="content">
            @yield('content')
            </div>
            <div class="footer">
                Email Generated Time:- {{ date('Y-m-d H:i:s') }}<br/>
                Logged User:- @if($loggedUser) {{$loggedUser->name}} [{{$loggedUser->u_code}}] @else N/A @endIf<br/>
            </div>
        </main>

    </div>
</body>
</html>
