@if(isset($products))

<table class="highlight">
    <thead class="grey">
        <tr>
            <th class="center-align">Brand Name</th>
            <th class="center-align">Target Value</th>
            <th class="center-align">Achiev. Value</th>
            <th class="center-align">Achiev. %</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $key=> $product)
        <tr >
            <td class="gray" >{{ $product['brand_name'] }}</td>
            <td>{{ $product['target_value'] }}</td>
            <td>{{ $product['ach_value'] }}</td>
            <td>{{ $product['ach_percent'] }}</td>
        </tr>
        @endForeach
    </tbody>
</table>
@endIf