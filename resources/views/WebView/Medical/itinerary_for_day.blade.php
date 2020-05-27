<html>

<head>
    <title>Itinerary for </title>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="{{ url('/css/style.css')}}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .image-letter{
            height: 130px;
            width: 130px;
            color: #fff;
            font-size: 6em;
            text-align: center;
            line-height: 130px;
            border-radius: 16px
        }

        .red {
            background:#ea6e6b;
        }

        .green {
            background:#f4c542
        }

        .yellow {
            background: #6fed7c
        }

        .card .card-content {
            padding-top:12px;
            padding-bottom: 0px;
        }

        .card {
             margin: .2rem 0 0rem 0
        }

        .card.horizontal{
            padding: 6px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="row">
            <div class="col s12">
                @forEach($customers as $customer)
                <div class="card horizontal">
                    <div class="card-image">
                        <div class="image-letter @switch($customer['status']) @case(1) green @break @case(-1) yellow @break @default red  @endswitch ">
                            @switch($customer['personType'])
                                @case(0)
                                    D
                                    @break
                                @case(1)
                                    C
                                    @break
                                @default
                                    O
                            @endswitch
                        </div>
                    </div>
                    <div class="card-stacked">
                        <div class="card-content">
                            <p>{{ $customer['personName'] }}</p>
                        </div>
                        <div class="card-action">
                            <a href="#">{{ $customer['personSpeciality'] }}</a>
                        </div>
                    </div>
                </div>
                @endForEach
            </div>
        </div>
    </div>
</body>

</html>
