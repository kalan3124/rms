@if(isset($ytdCusWiseSales))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered" id="data_table">  
               <thead style="background-color: #d9d9d9;">  
                    <tr> 
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Name</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Sub Town</td>  
                         <th style="border-right: 2px #87888a solid;" scope="col">Customer Class</td> 
                         @foreach ($period as $dt)
                              <th style="border-right: 2px #87888a solid;" scope="col">{{$dt->format('M')}}</td>
                         @endforeach 
                    </tr>  
               </thead>  
                    
               <tbody>
                    @foreach ($ytdCusWiseSales as $ytdCusWiseSale)
                         <tr>   
                              <td style="border-right: 2px #87888a solid;border-bottom: 2px #87888a solid;">{{$ytdCusWiseSale['chemist']}}</td>  
                              <td style="border-right: 2px #87888a solid;border-bottom: 2px #87888a solid;">{{$ytdCusWiseSale['chemist_name']}}</td> 
                              <td style="border-right: 2px #87888a solid;border-bottom: 2px #87888a solid;">{{$ytdCusWiseSale['sub_name']}}</td>  
                              <td style="border-right: 2px #87888a solid;border-bottom: 2px #87888a solid;">{{$ytdCusWiseSale['class']}}</td>
                              @foreach ($period as $dt)
                                   <td style="border-right: 2px #87888a solid;border-bottom: 2px #87888a solid;">{{$ytdCusWiseSale['month_'.$dt->format('m')]}}</td>
                              @endforeach   
                         </tr>
                    @endforeach  
               </tbody>  
          </table> 
     </div> 
</div>
<script type="text/javascript">
     $(document).ready( function () {
          var dataTable = $('#data_table').DataTable({
               language: {
                    searchPlaceholder: "Search here"
               }
          });
     });
     $(".dataTables_filter input").css({ "background" :"#e1e6ed" });
</script>
@endIf