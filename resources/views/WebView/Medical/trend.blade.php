<!DOCTYPE html>
  <html>
    <head>
      <!--Import Google Icon Font-->
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
      <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <link href="{{ url('/css/style.css')}}" rel="stylesheet" type="text/css">
      <style type="text/css">
        table {
            position: relative;
            max-width: 100vw;
            overflow: hidden;
            border-collapse: collapse;
        }
        td {
            padding:0;
            text-align:center;
        }

        th {
            height: 76px;
            font-size:.7em;
            text-align: center;
        }

        /*thead*/
        thead {
            position: relative;
            display: block; /*seperates the header from the body allowing it to be positioned*/
            max-width: 100vw;
            overflow: visible;
        }

        thead th {
            min-width: 200px;
            background-color:#9e9e9e !important
        }

        thead th:nth-child(1) {/*first cell in the header*/
            position: relative;
            display: block; /*seperates the first cell in the header from the header*/
            width: 200px;
            background:#9e9e9e
        }


        /*tbody*/
        tbody {
            position: relative;
            display: block; /*seperates the tbody from the header*/
            height: calc(100vh - 120px);
            overflow: scroll;
            width:100vw
        }

        tbody td {
            min-width: 200px;
        }

        tbody tr td:nth-child(1) {  /*the first cell in each tr*/
            position: relative;
            
            height: 30px;
            width: 200px;
            text-align:left;
            background:#fff;
        }

        .verticalTableHeader {
            text-align:center;
            white-space:nowrap;
            transform-origin:50% 50%;
            -webkit-transform: rotate(90deg);
            -moz-transform: rotate(90deg);
            -ms-transform: rotate(90deg);
            -o-transform: rotate(90deg);
            transform: rotate(90deg);
            
        }
        .verticalTableHeader:before {
            content:'';
            padding-top:110%;/* takes width as reference, + 10% for faking some extra padding */
            display:inline-block;
            vertical-align:middle;
        }
        
      </style>
    </head>

    <body>
        <div class="wrapper">
            <!-- Page Content goes here -->
            <div class="row">
                <div class="col s2"></div>
                <div class="col s8">
                <div class="card lighten-2">
                    <div class="">
                        <h5 class="center-align report-header">Trending Report</h5>
                    </div>
                    <div class="card-action">
                    </div>
                </div>
                </div>
                <div class="col s2"></div>
            </div>
            <table class="highlight table" >
            <thead class="grey" >
            <tr style="width: 10px;">
                <th>Sub Town Name</th>
                <th >Sub Town Code</th>
                @foreach ($product AS $pro)
                <th >{{$pro['product_name']}}</th>
                @endforeach
                <th >Grand Total</th>
                <th >Grand Qty</th>
            </tr>
            </thead>

            <tbody>
            @php
                $pro_sale = [];
                $index = 0;
                $total_value = 0;
            @endphp
            @foreach ($twn_wise_pro AS $town)
            <tr style="font-size: 11px">
            <td style="text-align:left;width: 10px;height: 10px;background-color: azure">{{$town['sub_twn_name']}}</td>
            <td>{{$town['sub_twn_code']}}</td>
            @php
                $grand_qty = 0;
                $grand_value = 0;
            @endphp
            @foreach ($product AS $pro)
            <td >
            @foreach ($town['product_details'] AS $pd)
                @if ($pd['product_id'] == $pro['product_id'])
                    @php
                        $grand_qty += $pd['qty'];
                        $grand_value += $pd['amount'];
                        $total_value += $pd['amount'];
                        if(isset($pro_sale[$index])){
                            $pro_sale[$index] += $pd['qty'];
                        }else{
                            $pro_sale[$index] = $pd['qty'];
                        }
                        $index++;
                    @endphp
                {{$pd['qty']}}
                @endif
            @endforeach
            </td>
            @endforeach
            @php
                $index = 0;
            @endphp
            <td >{{number_format($grand_value,2)}}</td>
            <td >{{$grand_qty}}</td>
            </tr>
            @endforeach
            <tr>
                <td style="background-color:#bdbdbd;font-weight:bold">Grand Total</td>
                <td style="background-color:#bdbdbd;font-weight:bold"></td>
                @php 
                $total_qty = 0;
                @endphp
                @foreach ($pro_sale AS $ps)
                @php 
                $total_qty += $ps;
                @endphp
                <td  style="background-color:#bdbdbd;font-weight:bold">{{$ps}}</td>
                @endforeach
                <td  style="background-color:#bdbdbd;font-weight:bold">{{number_format($total_value,2)}}</td>
                <td  style="background-color:#bdbdbd;font-weight:bold">{{$total_qty}}</td>
            </tr>
            </tbody>
        </table>
        </div>
        <script
            src="https://code.jquery.com/jquery-3.4.1.js"
            integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
            crossorigin="anonymous"></script>
      <!--JavaScript at end of body for optimized loading-->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
      <script type="text/javascript">
        $(document).ready(function() {

            var changePosition = function(e) { 
                $('thead').css("left", -$("tbody").scrollLeft());
                $('thead th:nth-child(1)').css("left", $("tbody").scrollLeft());
                $('tbody td:nth-child(1)').css("left", $("tbody").scrollLeft());
            };

            $('tbody').on('scroll', changePosition);
            $('body').on('touchmove',changePosition)

        })

      </script>
    </body>
  </html>