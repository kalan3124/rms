@if(isset($exeWiseSales))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>
                         <th style="border-right: 2px #87888a solid;" colspan="2"></th>
                         <th style="border-right: 2px #87888a solid;" colspan="2">Pharma Sales</th>
                         <th style="border-right: 2px #87888a solid;" colspan="2">Total Sales</th>
                    </tr>
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Executive Code</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Executive Name</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Current Day Sales</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Cumulative Sales</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Current Day Sales</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Cumulative Sales</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($exeWiseSales as $exeWiseSale)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['exe_code']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['exe_name']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['curr_day_sales']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['cum_sales']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['tot_curr_day_sales']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$exeWiseSale['tot_cum_sales']}}</td>  
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf