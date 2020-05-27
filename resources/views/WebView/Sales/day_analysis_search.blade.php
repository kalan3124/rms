@if(isset($daySales))
<div class="row">
     <div class="col-md-10">
          <table class="table table-bordered">
               <thead style="background-color: #d9d9d9;">
                    <tr>
                        <th style="border-right: 2px #87888a solid;" colspan="1"></th>
                        <th style="border-right: 2px #87888a solid;" colspan="1"></th>
                        <th style="border-right: 2px #87888a solid;" colspan="4"></th>
                        <th style="border-right: 2px #87888a solid;" colspan="4">CUMULATIVE</th>
                        <th style="border-right: 2px #87888a solid;" colspan="2"></th>
                    </tr>
                    <tr>
                         <th style="border-right: 2px #87888a solid;" scope="col">DATE</td>
                         <th style="border-right: 2px #87888a solid;" scope="col"></td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Day Target</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Sales Order Value</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Achievement</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Month Target</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Sales Order Value</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">Achievement</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">%</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">DEFFICT</td>
                         <th style="border-right: 2px #87888a solid;" scope="col">TO BE INVOICED</td>
                    </tr>
               </thead>

               <tbody>
                    @foreach ($daySales as $daySales)
                         <tr>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['date']))
                                {{$daySales['date']}}
                              @endif</td>
                               <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['date_name']))
                                {{$daySales['date_name']}}
                               @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['day_target']))
                                {{$daySales['day_target']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['sales_order_value']))
                                {{$daySales['sales_order_value']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['achi']))
                                  {{$daySales['achi']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['precentage']))
                                {{$daySales['precentage']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['month_target']))
                                {{$daySales['month_target']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['cu_order_value']))
                                {{$daySales['cu_order_value']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['cu_achi']))
                                {{$daySales['cu_achi']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['c_precentage']))
                                {{$daySales['c_precentage']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['defict']))
                                {{$daySales['defict']}}
                              @endif</td>
                              <td style="border-right: 2px #87888a solid;background-color:@if(isset($daySales['special'])) #7bdaed @endif;">@if (isset($daySales['to_be']))
                                {{$daySales['to_be']}}
                              @endif</td>
                         </tr>
                    @endforeach
               </tbody>
          </table>
     </div>
</div>
@endIf
