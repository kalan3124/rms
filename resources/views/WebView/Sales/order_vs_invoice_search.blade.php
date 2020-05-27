@if(isset($orders))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Code</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Name</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">SFA Order No</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">SFA Order Create Date</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">SFA Order Value</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">IFS Invoice No</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">IFS Invoice Date</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">IFS Invoice Value</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Difference</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($orders as $order)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$order['cus_code']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['cus_name']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$order['order_no']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['order_create_date']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['order_val']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['inv_no']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['inv_date']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['inv_val']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$order['diff']}}</td>  
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf