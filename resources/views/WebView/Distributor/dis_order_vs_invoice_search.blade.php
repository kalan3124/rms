@if(isset($orders))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Date</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Distributor Name</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Agency Name</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Product Code</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Product Name</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Pack Size</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Order No</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Order Qty</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Order Value</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Invoiced Qty</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Invoiced Value</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Losed Sales Qty</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Losed Sales Value</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($orders as $order)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$order['date']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['dis_name']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$order['agency_name']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['pro_code']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['pro_name']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['pack_size']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['order_no']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['order_qty']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['order_value']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['inv_qty']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['inv_value']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['losed_qty']}}</td>
                              <td style="border-right: 2px #87888a solid;">{{$order['losed_value']}}</td>
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf