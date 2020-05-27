<html>

<head>
    <style>
        @page {
            margin: 0 24px;
        }

        header,
        header .logo {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
        }

        footer {
            position: fixed;
            bottom: 100px;
            left: 0px;
            right: 0px;
        }

        table {
            page-break-inside: auto
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto
        }

        thead {
            display: table-header-group
        }

        tfoot {
            display: table-footer-group
        }

        body {
            padding-top: 228px;
            padding-bottom: 200px;
            font-family: sans-serif;
        }

        .pagenum::before {
            content: "Page " counter(page);
        }

        .return-copy {
            font-size: 10pt;
            font-weight: 600;
            margin-top: 10px;
            /* border-radius: 4px;
            border: 4px solid;
            padding: 2px;
            display: block;
            width: 150px;
            position: fixed;
            top: 8px;
            right: 8px; */
        }

        .dist-name {
            margin-top: 5px;
            font-weight: 600;
        }

        .border {
            border: 1px solid;
            position: absolute;
            border-radius: 6px;
        }

        .border-total {
            border: 1px solid;
            position: absolute;
            border-bottom-right-radius:6px;
            border-bottom-left-radius:6px;
            /* border-radius: 6px; */
        }

        .border-table {
            border: 1px solid;
            position: absolute;
            border-bottom-right-radius:6px;
            border-bottom-left-radius:6px;
        }

        .border-right {
            right: 0;
        }

        .border-left {
            left: 0;
        }

        .border-top {
            height: 100px;
            top: -4px;
        }

        .border-bottom {
            height: 86px;
            top: 0px;
        }

        .border-left {
            width: 768px;
        }

        .border-right {
            width: 348px;
        }

        .content th {
            padding-left:2px;
            /* background:#ebebeb; */
            padding-bottom:8px;
            border-left:solid 1px;
            border-bottom:solid 8px #fff;
        }

        .content td {
            padding-left:4px;
            text-align: right
        }

        .content-border {
            position:fixed;
            background:#000;
            height:578px; 
            width:1px; 
            top:262px;
            z-index: 2;
        }

        td.align-right {
            text-align: right
        }

        td.align-left {
            text-align: left
        }

        .content-border-main {
            /* background: no-repeat 0 20% url(data:image/jpeg;base64,{{$watermark}}); */
            background-size: 120%;
            position:fixed;
            width:100%;
            border:solid 1px;
            height:578px; 
            top:262px; 
            border-radius: 8px; 
            z-index: -1
        }
        
        .border-height {
            height: 62px;
        }
    </style>
</head>

<body>
    <header style="text-align: center">
        <img class="logo" width="100" src="data:image/jpeg;base64,{{$logo}}" />
        <h2 style="font-weight:500; margin:0">Sunshine Healthcare Lanka Ltd</h2>
        <div style="font-size: 9pt">27-4/1,York Arcade Building, York Arcade Road,Colombo - 1,Sri Lanka.</div>
        <div style="font-size: 9pt">T : +94 11 470 2500, F : +94 11 470 2500, W : sunshinehealthcare.com</div>
        <div style="font-size: 7pt">Co.Reg.No PB 355</div>

        <div class="dist-name">
            {{$dis_name}}
        </div>

        <div class="return-copy">
           GOOD RETURN NOTE / CREDIT NOTE
        </div>

        <div style="position: relative;margin-top: 6px">
            <div class="border border-top border-left" />
            {{-- <div class="border border-top border-right" /> --}}
            <table style="font-size:8pt; height:96px;" width="100%" border="0">
                <tbody>
                    <tr class="">
                        <td width="120">Customer Code</td>
                        <td width="4">:</td>
                        <td width="120">{{$customer_code}}</td>
                        <td></td>
                        <td width="120">GRTN/C/Note No</td>
                        <td width="4">:</td>
                        <td width="120">{{$return_number}}</td>
                    </tr>
                    <tr>
                        <td width="120">Customer Name</td>
                        <td width="4">:</td>
                        <td width="120">{{$customer_name}}</td>
                        <td></td>
                        <td width="120">Credit Note Date</td>
                        <td width="4">:</td>
                        <td width="120">{{$return_date}}</td>
                    </tr>
                    <tr>
                        <td width="120">Address</td>
                        <td width="4">:</td>
                        <td width="120">{{$address}}</td>
                        <td></td>
                        <td width="120">Ref.Invoice No</td>
                        <td width="4">:</td>
                    <td width="120">{{$order_return_no}}</td>
                    </tr>
                    <tr>
                         <td width="120">Ref.Location</td>
                         <td width="4">:</td>
                         <td width="120"></td>
                         <td></td>
                         <td width="120">Invoice Date</td>
                         <td width="4">:</td>
                         <td width="120">{{$order_return_datey}}</td>
                     </tr>
                     <tr>
                         <td width="120">Sales Rep's Name</td>
                         <td width="4">:</td>
                         <td width="120">{{$rep}}</td>
                         <td></td>
                         <td width="120"></td>
                         <td width="4"></td>
                         <td width="120"></td>
                     </tr>
                </tbody>
            </table>
        </div>
    </header>

    <footer>
        <table style="font-size: 8pt" width="100%">
            <tr style="font-size: 7pt;">
                <td style="text-align: left;padding-top:32px" width="200">
                    Print By : 
                </td>
                <td />
                <td style="text-align: right;padding-top:32px" width="200">
                    <span class="pagenum"></span> of 
                </td>
            </tr>
        </table>
    </footer>

    <div class="content-border-main"/>
    <div class="content-border" style="right:84px;"/>
    <div class="content-border" style="right:158px;"/>
    <div class="content-border" style="right:214px;"/>
    <div class="content-border" style="right:280px;"/>
    <div class="content-border" style="right:356px;"/>
    <div class="content-border" style="right:432px;"/>
    {{-- <div class="content-border" style="right:530px;"/> --}}


    <div style="font-size: 9pt;position:relative" class="content">
        <table width="100%">
            <thead style="margin-bottom:8px">
                <tr>
                    <th>DESCRIPTION</th>
                    {{-- <th style="width:90px;">RETAIL PRICE</th> --}}
                    <th style="width:70px;">PACK SIZE</th>
                    <th style="width:70px;">QUANTITY</th>
                    <th style="width:60px;">BONUS</th>
                    <th style="width:50px;">PRICE</th>
                    <th style="width:68px;">DIS %</th>
                    <th style="width:80px;">VALUE RS.</th>
                </tr>
            </thead>
            <tbody style="border-top-left-radius: 16px; border-top-right-radius:16px;">
                @foreach ( $lines as $line )
                    <tr>
                         <td class="align-left" >{{$line['product_name']}} <br>{{$line['batch_code']}} {{$line['pro_code']}} {{$line['batch_exp']}}<br>{{$line['reason']}}</td>
                         {{-- <td></td> --}}
                         <td>{{$line['pack_size']}}</td>
                         <td>{{$line['qty']}}</td>
                         <td>{{$line['bonus']}}</td>
                         <td>{{$line['sale_price']}}</td>
                         <td></td>
                         <td style="background:">{{$line['amount']}}</td>
                    </tr>
                @endforeach
                    
            </tbody>
        </table>
    </div>


    <div style="position: absolute;bottom:-82px" >
            <div style="position: relative;" class="summery">
                {{-- <div class="border border-bottom border-left" /> --}}
                <div class="border-total border-bottom  border-left border-height"/>
                <table style="font-size:8pt; height:96px;" width="100%" border="0">
                    <tbody>
                        <tr class="">
                            <td width="244"></td>
                            <td></td>
                            <td style="text-align: left" width="120">Total Value</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60">{{$gross_value}}</td>
                        </tr>
                        <tr class="">
                            <td width="244"><b></b></td>
                            <td></td>
                            <td style="text-align: left" width="120">Total Discount</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60">{{$discount}}</td>
                        </tr>
                        <tr>
                            <td width="244"></td>
                            <td></td>
                            <td width="120"></td>
                            <td width="4"></td>
                            <td style="background:" width="60"></td>
                        </tr>
                        <tr>
                            <td width="244"></td>
                            <td></td>
                            <td style="text-align: left" width="120">Total Net Value</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60">{{$net_value}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="position: absolute;bottom:-155px" >
            <div style="position: relative;" class="summery">
                {{-- <div class="border border-bottom border-left" /> --}}
                <div class="border border-bottom  border-left border-height"/>
                <table style="font-size:8pt; height:96px;" width="100%" border="0">
                    <tbody>
                        <tr class="">
                            <td width="120">Recived By</td>
                            <td width="4">:</td>
                            <td width="120"></td>
                            <td></td>
                            <td width="120">Authrised By</td>
                            <td width="4">:</td>
                            <td width="120">                            </td>
                        </tr>
                        <tr>
                            <td width="120">Manger - Stores</td>
                            <td width="4">:</td>
                            <td width="120"></td>
                            <td></td>
                            <td width="120">Date</td>
                            <td width="4">:</td>
                            <td width="120">                            </td>
                        </tr>
                        <tr>
                            <td width="120">Entered By</td>
                            <td width="4">:</td>
                            <td width="120"></td>
                            <td></td>
                            <td width="120"></td>
                            <td width="4"></td>
                            <td width="120"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</body>

</html>