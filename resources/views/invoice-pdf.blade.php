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

        .customer-copy {
            font-size: 8pt;
            font-weight: 600;
            border-radius: 4px;
            border: 4px solid;
            padding: 2px;
            display: block;
            width: 150px;
            position: fixed;
            top: 8px;
            right: 8px;
        }

        .dist-name {
            margin-top: 16px;
            font-weight: 600;
            font-size: 15pt;
        }

        .border {
            border: 1px solid;
            position: absolute;
            border-radius: 6px;
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
            width: 410px;
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
        
    </style>
</head>

<body>
    <header style="text-align: center">
        {{-- <img class="logo" width="100" src="data:image/jpeg;base64,{{$logo}}" /> --}}
        <h4 style="font-weight:500; margin:0">Sunshine Healthcare Lanka Ltd</h4>
        <div style="font-size: 9pt">27-4/1,York Arcade Building, York Arcade Road,Colombo - 1,Sri Lanka.</div>
        <div style="font-size: 9pt">T : +94 11 470 2500, F : +94 11 470 2500, W : sunshinehealthcare.com</div>
        <div style="font-size: 7pt">Co.Reg.No PB 355</div>
        {{-- <div class="customer-copy"> --}}
            {{-- {{$original?"ORIGINAL":"CUSTOMER COPY"}} --}}
        {{-- </div> --}}

        <div class="dist-name">
            {{$dis_name}}
        </div>

        <div style="position: relative;margin-top: 16px">
            <div class="border border-top border-left" style="width:372px"/>
            <div class="border border-top border-right" style="width:372px;margin-right: 15px"/>
            <table style="font-size:8pt; height:96px;" width="100%" border="0">
                <tbody>
                    <tr class="">
                        <td width="120">Customer Code</td>
                        <td width="4">:</td>
                        <td width="120">{{ $customer_code }}</td>
                        <td></td>
                        <td width="120"><div style="margin-left: -40px">Invoice No</div></td>
                        <td width="4">:</td>
                        <td width="120">{{ $invoice_number }}</td>
                    </tr>
                    <tr>
                        <td width="120">Customer Name</td>
                        <td width="4">:</td>
                        <td width="120">{{ $customer_name }}</td>
                        <td></td>
                        <td width="120"><div style="margin-left: -40px">Invoice Date</div></td>
                        <td width="4">:</td>
                        <td width="120">{{ $invoice_date }}</td>
                    </tr>
                    <tr>
                        <td width="120">Address</td>
                        <td width="4">:</td>
                        <td width="120">{{  $address }}</td>
                        <td></td>
                        <td width="120"><div style="margin-left: -40px">W/H Code</div></td>
                        <td width="4">:</td>
                        <td width="120">{{ $wh_code }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </header>

    <footer>
        <table style="font-size: 8pt" width="100%">
            <tr style="text-align: center">
                <td width="200">
                    <div style="height:24px">............................</div>
                    <div>Received By</div>
                </td>
                <td />
                <td width="200">
                    <div style="height:24px">............................</div>
                    <div>Authorized By</div>
                </td>
            </tr>
            <tr style="font-size: 7pt;">
                <td style="text-align: left;padding-top:28px" width="200">
                    Print By : {{$printed_user}}
                </td>
                <td />
                <td style="text-align: right;padding-top:28px" width="200">
                    <span class="pagenum"></span> of {{$page_count}}
                </td>
            </tr>
        </table>
    </footer>

    {{-- <div class="content-border-main"/> --}}
    {{-- <div class="content-border" style="right:84px;"/>
    <div class="content-border" style="right:158px;"/>
    <div class="content-border" style="right:214px;"/>
    <div class="content-border" style="right:280px;"/>
    <div class="content-border" style="right:356px;"/>
    <div class="content-border" style="right:432px;"/>
    <div class="content-border" style="right:530px;"/> --}}


    <div style="font-size: 9pt;position:relative" class="content">
        <table width="100%">
            <thead style="margin-bottom:8px">
                <tr>
                    <th>DESCRIPTION</th>
                    <th style="width:90px;">RETAIL PRICE</th>
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
                    <td class="align-left" >{{$line['product_name']}} <br>{{$line['batch_code']}} {{$line['pro_code']}} {{$line['batch_exp']}}</td>
                        <td>{{$line['base_price']}}</td>
                        <td>{{$line['pack_size']}}</td>
                        <td>{{$line['qty']}}</td>
                        <td>{{$line['bonus']}}</td>
                        <td>{{$line['sale_price']}}</td>
                        <td>{{$line['discount']}}</td>
                        <td><div style="margin-right: 15px">{{$line['amount']}}</div></td>
                    </tr>
                @endforeach
                    
            </tbody>
        </table>
    </div>


    <div style="position: absolute;bottom:-96px">
            <div style="position: relative;" class="summery">
                <div class="border border-bottom border-left" style="width:372px"/>
                <div class="border border-bottom border-right" style="width:372px;margin-right: 15px"/>
                <table style="font-size:8pt; height:96px;" width="100%" border="0">
                    <tbody>
                        <tr class="">
                            <td width="244"></td>
                            <td></td>
                            <td style="text-align: right" width="120">Gross Value</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60"><div style="margin-right: 15px">{{ $gross_value }}</div></td>
                        </tr>
                        <tr class="">
                            <td width="244"><b>Note:</b></td>
                            <td></td>
                            <td style="text-align: right" width="120">Total Discount</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60"><div style="margin-right: 15px">{{ $discount }}</div></td>
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
                            <td style="text-align: right" width="120">Net Value</td>
                            <td width="4">:</td>
                            <td style="background:; text-align:right" width="60"><div style="margin-right: 15px">{{ $net_value }}</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</body>

</html>