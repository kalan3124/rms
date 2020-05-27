@if(isset($products))

<table class="highlight">
    <thead class="grey">
        <tr>
            <th colspan="3" class="center-align"></th>
            <th colspan="4" class="center-align">Current Month</th>
            <th colspan="2" class="center-align">Last Year Current Month</th>
            <th colspan="2" class="center-align">YTD</th>
            <th colspan="1" class="center-align"></th>
        </tr>
        <tr>
            <th class="center-align">Item ID</th>
            <th class="center-align">Brand Name</th>
            <th class="center-align">Price</th>
            <th class="center-align">Target Qty</th>
            <th class="center-align">Achiev. Qty</th>
            <th class="center-align">Target Value Rs.</th>
            <th class="center-align">Achiev. Value Rs.</th>
            <th class="center-align">Achiev. Qty</th>
            <th class="center-align">Achiev. Value Rs.</th>
            <th class="center-align">Achiev. Qty</th>
            <th class="center-align">Achiev. Value Rs.</th>
            <th colspan="1" class="center-align">Growth</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $key=> $product)
        <tr class="{{ count($products)==$key+1?"grey":"" }}" >
            <td>{{ $product['product_code'] }}</td>
            <td>{{ isset($product['product_name'])?$product['product_name']:"" }}</td>
            <td>{{ $product['price'] }}</td>
            <td>{{ $product['cur_targ_qty'] }}</td>
            <td>{{ $product['cur_ach_qty'] }}</td>
            <td>{{ $product['cur_targ_val'] }}</td>
            <td>{{ $product['cur_ach_val'] }}</td>
            <td>{{ $product['lst_yr_cur_mnth_ach_qty'] }}</td>
            <td>{{ $product['lst_yr_cur_mnth_cur_ach_val'] }}</td>
            <td>{{ $product['ytd_ach_qty'] }}</td>
            <td>{{ $product['ytd_cur_ach_val'] }}</td>
            <td>{{ $product['growth'] }}</td>
        </tr>
        @endForeach
    </tbody>
</table>
@endIf