@if(isset($principalWiseTargets))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Principal</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Target</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Achivement</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Balance</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($principalWiseTargets as $principalWiseTarget)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$principalWiseTarget['principal']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$principalWiseTarget['target']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$principalWiseTarget['achi']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$principalWiseTarget['achi_%']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$principalWiseTarget['balance']}}</td>  
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf