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
    @if(isset($returns) && count($returns)>0)
    @foreach($returns as $res)
    <tr>
        <td>{{ $res->Unit }}</td>
        <td>{{ $res->DocType }}</td>
        <td>{{ $res->TerritoryCode }}</td>
        <td>{{ $res->DelivaryTown }}</td>
        <td>{{ $res->Supplier }}</td>
        <td>{{ $res->LocationCode }}</td>
        <td>{{ $res->CreditNoteNo }}</td>
        <td>{{ $res->CustomerOrderReference }}</td>
        <td>{{ $res->OrderNumber }}</td>
        <td>{{ $res->CreditDate }}</td>
        <td>{{ $res->RetailerCode }}</td>
        <td>{{ $res->ExecutiveCode }}</td>
        <td>{{ $res->ProductCode }}</td>
        <td>{{ $res->UnitPrice }}</td>
        <td>{{ $res->ReturnQty }}</td>
        <td>{{ $res->ReturnBonusQty }}</td>
        <td>{{ $res->CreditLineGoodsValue }}</td>
        <td>{{ $res->CreditLineDiscountValue }}</td>
        <td>{{ $res->CreditLineVatValue }}</td>
        <td>{{ $res->CreditLineGrsValue }}</td>
    </tr>
    @endforeach
    @endif

    @if(isset($returnBonus) && count($returnBonus)>0)
    @foreach($returnBonus as $bonus)
    <tr>
        <td>{{ $bonus->Unit }}</td>
        <td>{{ $bonus->DocType }}</td>
        <td>{{ $bonus->TerritoryCode }}</td>
        <td>{{ $bonus->DelivaryTown }}</td>
        <td>{{ $bonus->Supplier }}</td>
        <td>{{ $bonus->LocationCode }}</td>
        <td>{{ $bonus->CreditNoteNo }}</td>
        <td>{{ $bonus->CustomerOrderReference }}</td>
        <td>{{ $bonus->OrderNumber }}</td>
        <td>{{ $bonus->CreditDate }}</td>
        <td>{{ $bonus->RetailerCode }}</td>
        <td>{{ $bonus->ExecutiveCode }}</td>
        <td>{{ $bonus->ProductCode }}</td>
        <td>{{ $bonus->UnitPrice }}</td>
        <td>{{ $bonus->ReturnQty }}</td>
        <td>{{ $bonus->ReturnBonusQty }}</td>
        <td>{{ $bonus->CreditLineGoodsValue }}</td>
        <td>{{ $bonus->CreditLineDiscountValue }}</td>
        <td>{{ $bonus->CreditLineVatValue }}</td>
        <td>{{ $bonus->CreditLineGrsValue }}</td>
    </tr>
    @endforeach
    @endif
    </tbody>
    </table>