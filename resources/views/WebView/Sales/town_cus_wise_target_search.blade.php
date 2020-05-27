@if(isset($townCusTargets))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">  
               <thead style="background-color: #d9d9d9;">  
                    <tr>   
                         <th style="border-right: 2px #87888a solid;" scope="col">Town Name</td>  
                         {{-- <th style="border-right: 2px #87888a solid;" scope="col">Customer</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Name</td>   --}}
                         <th style="border-right: 2px #87888a solid;" scope="col">Target</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Achivement</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Balance</td>  
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($townCusTargets as $townCusTarget)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['twn_name']}}</td>  
                              {{-- <td style="border-right: 2px #87888a solid;">{{$townCusTarget['chemist_code']}}</td> 
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['chemist_name']}}</td>   --}}
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['target']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['achi']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['achi_%']}}</td>  
                              <td style="border-right: 2px #87888a solid;">{{$townCusTarget['balance']}}</td>  
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
@endIf