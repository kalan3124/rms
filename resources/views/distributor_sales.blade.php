<table>
    <thead>
    <tr>
        <th style="text-align:center" colspan="{{ count($headers) }}">
            <b>
                {{ $title }}
            </b>
        </th>
    </tr>
    <tr>
        @foreach($headers as $head=>$name)
        <td>{{ $name }}</td>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @if(isset($invoices) && count($invoices)>0)
    @foreach($invoices as $res)
        <tr>
            <td>{{ $res->Unit }}</td>
            <td>{{ $res->DocType }}</td>
            <td>{{ $res->TerritoryCode }}</td>
            <td>{{ $res->DelivaryTown }}</td>
            <td>{{ $res->Supplier }}</td>
            <td>{{ $res->StockTerritory }}</td>
            <td>{{ $res->InvoiceNo }}</td>
            <td>{{ $res->CustomerOrderReference }}</td>
            <td>{{ $res->OrderNumber }}</td>
            <td>{{ $res->InvoiceDate }}</td>
            <td>{{ $res->RetailerCode }}</td>
            <td>{{ $res->ExecutiveCode }}</td>
            <td>{{ $res->ProductCode }}</td>
            <td>{{ $res->UnitPrice }}</td>
            <td>{{ $res->InvoiceQty }}</td>
            <td>{{ $res->BonusQty }}</td>
            <td>{{ $res->LineGoodsValue }}</td>
            <td>{{ $res->LineDiscountValue }}</td>
            <td>{{ $res->LineVatValue }}</td>
            <td>{{ $res->LineGrsValue }}</td>
        </tr>
    @endforeach
    @endif

    @if(isset($invoiceBonus) && count($invoiceBonus)>0)
    @foreach($invoiceBonus as $bonus)
        <tr>
            <td>{{ $bonus->Unit }}</td>
            <td>{{ $bonus->DocType }}</td>
            <td>{{ $bonus->TerritoryCode }}</td>
            <td>{{ $bonus->DelivaryTown }}</td>
            <td>{{ $bonus->Supplier }}</td>
            <td>{{ $bonus->StockTerritory }}</td>
            <td>{{ $bonus->InvoiceNo }}</td>
            <td>{{ $bonus->CustomerOrderReference }}</td>
            <td>{{ $bonus->OrderNumber }}</td>
            <td>{{ $bonus->InvoiceDate }}</td>
            <td>{{ $bonus->RetailerCode }}</td>
            <td>{{ $bonus->ExecutiveCode }}</td>
            <td>{{ $bonus->ProductCode }}</td>
            <td>{{ $bonus->UnitPrice }}</td>
            <td>{{ $bonus->InvoiceQty }}</td>
            <td>{{ $bonus->BonusQty }}</td>
            <td>{{ $bonus->LineGoodsValue }}</td>
            <td>{{ $bonus->LineDiscountValue }}</td>
            <td>{{ $bonus->LineVatValue }}</td>
            <td>{{ $bonus->LineGrsValue }}</td>
        </tr>
    @endforeach
   @endif
    </tbody>
</table>