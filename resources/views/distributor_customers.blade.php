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
    @foreach($results as $res)
    <tr>
        <td>{{ $res->CustomerCode }}</td>
        <td>{{ $res->CustomerName }}</td>
        <td>{{ $res->CreditLimit }}</td>
        <td>{{ $res->SettlementTermsCode }}</td>
        <td>{{ $res->TypeCode }}</td>
        <td>{{ $res->TypeName }}</td>
        <td>{{ $res->GroupCode }}</td>
        <td>{{ $res->GroupName }}</td>
        <td>{{ $res->ClassCode }}</td>
        <td>{{ $res->ClassName }}</td>
        <td>{{ $res->TownCode }}</td>
        <td>{{ $res->TownName }}</td>
        <td>{{ $res->TerritoryCode }}</td>
        <td>{{ $res->TerritoryName }}</td>
        <td>{{ $res->DistrictName }}</td>
        <td>{{ $res->Category }}</td>
        <td>{{ $res->IsActive }}</td>
        <td>{{ $res->Infered }}</td>
        <td>{{ $res->ValidFrom }}</td>
    </tr>
    @endforeach
    </tbody>
</table>