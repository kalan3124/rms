<html>
    <head>
        <style>
            @page { margin: 100px 25px;}
            header { position: fixed; top: -60px; left: 0px; right: 0px; }
            footer { position: fixed; bottom: -10px; left: 0px; right: 0px; }

            table { page-break-inside:auto }
            tr    { page-break-inside:avoid; page-break-after:auto }
            thead { display:table-header-group }
            tfoot { display:table-footer-group }

            body {
                padding-top:150px;
                font-family: sans-serif;
            }

            .pagenum::before {
                content: "Page " counter(page);
            }
            
        </style>
    </head>
    <body>
        <header style="text-align: center;" >
            <h2 style="font-weight:500; margin:0" >Sunshine Healthcare Lanka Ltd</h2>
            <div style="font-size: 9pt" >27-4/1,York Arcade Building, York Arcade Road,Colombo - 1,Sri Lanka.</div>
            <div style="font-size: 9pt">T : +94 11 470 2500, F : +94 11 470 2500, W : sunshinehealthcare.com</div>
            <div style="font-size: 7pt">Co.Reg.No PB 355</div>
            <h4>GOODS RECEIVED ORDER</h4>
            <table style="font-size:8pt" width="100%" border="0" >
                <tbody>
                    <tr>
                        <td width="120" >PO No</td>
                        <td width="4">:</td>
                        <td width="120">{{ $po_number }}</td>
                        <td></td>
                        <td width="120" >GRN No</td>
                        <td width="4">:</td>
                        <td width="120">{{ $grn_number }}</td>
                    </tr>
                    <tr>
                        <td width="120" >Supplier Code</td>
                        <td width="4">:</td>
                        <td width="120">{{ $dis_code }}</td>
                        <td></td>
                        <td width="120" >GRN Date</td>
                        <td width="4">:</td>
                        <td width="120">{{ $grn_date }}</td>
                    </tr>
                    <tr>
                        <td width="120" >Supplier Name</td>
                        <td width="4">:</td>
                        <td width="120">{{  $dis_name }}</td>
                        <td></td>
                        <td width="120" >Received Site</td>
                        <td width="4">:</td>
                        <td width="120">{{ $site_name }}</td>
                    </tr>
                </tbody>
            </table>
        </header>

        <footer>
            <table style="font-size: 8pt" width="100%" >
                <tr style="text-align: center" >
                    <td width="200">
                        <div style="height:24px" >............................</div>
                        <div>Received By</div>
                    </td>
                    <td/>
                    <td width="200">
                        <div style="height:24px" >............................</div>
                        <div>Authorized By</div>
                    </td>
                </tr>
                <tr style="font-size: 7pt;" >
                    <td style="text-align: left;padding-top:28px" width="200">
                        Print By : {{$printed_user}}
                    </td>
                    <td/>
                    <td style="text-align: right;padding-top:28px" width="200">
                        <span class="pagenum"></span> of {{$page_count}}
                    </td>
                </tr>
            </table>
        </footer>
        <table width="100%" style="font-size: 8pt" >
            <thead>
                <tr >
                    <th width="28" >Line  No</th>
                    <th width="60">Parti Code</th>
                    <th>Descripton</th>
                    <th width="40">UOM</th>
                    <th width="46">Batich No</th>
                    <th width="46">Received Qty</th>
                    <th width="46">Location No</th>
                    <th width="46">Expiry Datie</th>
                </tr>
            </thead>
            <tbody>
                @foreach ( $items as $item) 
                <tr>
                    <td>{{ $item['line_number'] }}</td>
                    <td>{{ $item['product_code'] }}</td>
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['uom'] }}</td>
                    <td>{{ $item['batch_code'] }}</td>
                    <td>{{ $item['qty'] }}</td>
                    <td>{{ $item['loc_num'] }}</td>
                    <td>{{ $item['expire_date'] }}</td>
                </tr>
                @endforeach
               

            </tbody>
        </table>
    </body>
</html>