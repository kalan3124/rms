@if(isset($cusWiseSales))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr> 
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Name</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Target</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Achievement</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Balance</td> 
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($cusWiseSales as $cusWiseSale)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['chemist']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['chemist_name']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['target']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['achi']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['ach_%']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$cusWiseSale['balance']}}</td>  
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf