<html>

<head>
    <title>Itinerary Towns </title>
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
        table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        }

        td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        width: 50px
        }

        th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        width: 50px
        }

        tr:nth-child(even) {
        background-color: #dddddd;
        }
    </style>
</head>

<body>
    <div class="wrapper">
         <br> <br> <br>
        <div class="row">
            <div class="col s12">
                    <div class = "card-panel center">
                            <h5>Itinerary Towns For Day</h5>       
                    </div>
                    <table>
                            <tr>
                              <th>Dates</th>
                              <th>Towns</th>
                            </tr>
                        @foreach ($mrUser as $mrData)
                            <tr>
                                <td>{{ $mrData['date'] }}</td>
                                @if ($mrData)
                                    <td>{{ $mrData['ar_name'] }}</td>
                                @endif
                                
                            </tr>    
                        @endforeach 
                    </table>
            </div>
        </div>
    </div>
</body>

</html>
