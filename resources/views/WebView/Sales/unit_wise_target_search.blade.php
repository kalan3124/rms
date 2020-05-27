@if(isset($townWiseTargets))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Product Code IFS</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Product</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Target</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Achivement</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Balance Value</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($townWiseTargets as $townWiseTarget)
                         <tr style="background-color: @if ($townWiseTarget['ach_%'] >= 100)
                              #fab2ac
                         @endif">   
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['pro_code']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['pro_name']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['target']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['achi']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['ach_%']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townWiseTarget['balance']}}</td>   
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf