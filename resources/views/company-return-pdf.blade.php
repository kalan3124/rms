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

        <div class="dist-name">
            {{$distributor->name}}
        </div>

        <div style="position: relative;margin-top: 16px">
            <div class="border border-top border-left" style="width:402px"/>
            <div class="border border-top border-right" style="width:342px;margin-right: 15px"/>
            <table style="font-size:8pt; height:96px;" width="100%" border="0">
                <tbody>
                    <tr>
                        <td width="120">Company Return No</td>
                        <td width="4">:</td>
                        <td width="120">{{ $number }}</td>
                        <td></td>
                        <td width="120">Confirmed Time</td>
                        <td width="4">:</td>
                        <td width="120">{{ $confirmedTime }}</td>
                    </tr>
                    <tr>
                        <td width="120">GRN Number</td>
                        <td width="4">:</td>
                        <td width="120">{{ $goodReceivedNote->grn_no }}</td>
                        <td></td>
                        <td width="120">Created Time</td>
                        <td width="4">:</td>
                        <td width="120">{{ $createdTime }}</td>
                    </tr>
                    <tr>
                        <td width="120">Remark</td>
                        <td width="4">:</td>
                        <td width="120">{{  $remark }}</td>
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
                    <span class="pagenum"></span> of {{$pageCount}}
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
                    <th>PRODUCT</th>
                    <th style="width:90px;">BATCH</th>
                    <th style="width:70px;">EXPIRE</th>
                    <th style="width:50px;">PRICE</th>
                    <th style="width:68px;">SALABLE</th>
                    <th style="width:70px;">RECEIVED QTY</th>
                    <th style="width:60px;">RETURN QTY</th>
                    <th style="width:80px;">RETURN VALUE</th>
                </tr>
            </thead>
            <tbody style="border-top-left-radius: 16px; border-top-right-radius:16px;">
                @foreach ( $lines as $line )
                    <tr>
                        <td class="align-left" >{{$line->product? $line->product->product_name: "N/A"}}</td>
                        <td class="align-left" >{{$line->batch? $line->batch->db_code: "N/A"}}</td>
                        <td class="align-left" >{{$line->batch? $line->batch->db_expire: "N/A"}}</td>
                        <td>{{$line->batch? $line->batch->db_price: "0.00"}}</td>
                        <td style="text-align:center" >{{$line->crl_salable? "YES": "NO"}}</td>
                        <td>{{$line->goodReceivedNoteLine? $line->goodReceivedNoteLine->grnl_qty: 0}}</td>
                        <td>{{$line->crl_qty}}</td>
                        <td><div style="margin-right: 15px">{{$line->crl_qty * ($line->batch? $line->batch->db_price: 0.00) }}</div></td>
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
                            <td style="text-align: right" width="120"></td>
                            <td width="4"></td>
                            <td style="text-align:right" width="60"><div style="margin-right: 15px"></div></td>
                        </tr>
                        <tr class="">
                            <td width="244"><b></b></td>
                            <td></td>
                            <td style="text-align: right" width="120"></td>
                            <td width="4"></td>
                            <td style=" text-align:right" width="60"><div style="margin-right: 15px"></div></td>
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
                            <td style="text-align: right" width="120">Amount</td>
                            <td width="4">:</td>
                            <td style="text-align:right" width="60"><div style="margin-right: 15px">{{ $grossValue }}</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</body>

</html>
