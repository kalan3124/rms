<!DOCTYPE html>
  <html>
     <head>
     <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <!--Import Google Icon Font-->
     <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
     <!--Import materialize.css-->
     <!-- Compiled and minified CSS -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
     <!--Let browser know website is optimized for mobile-->
     <link href="{{ url('/css/style.css')}}" rel="stylesheet" type="text/css">
     <style type="text/css">
          @media all and (max-width:100px) {
               .modal {
               position:absolute;
               left:-12%;
               height: 5px;
               }
          }
     </style>
    <body>
     <div class="container">
          <div class="wrapper">
               <div class="row">
                    <div class="col s2"></div>
                    <div class="col s8">
                    <div class="card lighten-2">
                         <div class="">
                              <h3 class="center-align report-header">Productivity Report</h3>
                         </div>
                    </div>
                    </div>
                    <div class="col s2"></div>
                    <div class="col s8">
                    </div>
               </div>
          </div>
     </div>
     <div class="row">
          <div class="col-md-12">
               <table class="table table-bordered">  
                    <thead style="background-color: #d9d9d9;">  
                         <tr>
                              <th style="border-right: 2px #87888a solid;" colspan="3" class="center-align"></th>
                              <th style="border-right: 2px #87888a solid;" colspan="6" class="center-align">Current Date</th>
                              <th style="border-right: 2px #87888a solid;" colspan="6" class="center-align">Cumulative for the MONTH</th>
                         </tr>
                         <tr>   
                              <th style="border-right: 2px #87888a solid;" scope="col">Territory Code</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Territory Name</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Executive Name</td> 

                              <th style="border-right: 2px #87888a solid;" scope="col">Schedule calls</td>
                              <th style="border-right: 2px #87888a solid;" scope="col">No of products</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">No of customer</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Booking Rate</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Call Rate %</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Difference</td>
                                   
                              <th style="border-right: 2px #87888a solid;" scope="col">Schedule calls</td>
                              <th style="border-right: 2px #87888a solid;" scope="col">No of products</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">No of customer</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Booking Rate</td>  
                              <th style="border-right: 2px #87888a solid;" scope="col">Call Rate %</td>  
                              {{-- <th style="border-right: 2px #87888a solid;" scope="col">Difference</td> --}}
                         </tr>  
                    </thead>  
                         
                    <tbody>
                         @foreach ($productivities as $productivity)
                              <tr>   
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['terr_code']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['terr_name']}}</td> 
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['exe_code']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['exe_name']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['sche_call']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['no_of_pro']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['no_of_cus']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['booking_rate']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['call_rate']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['c_sche_call']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['c_no_of_pro']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['c_no_of_cus']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['c_booking_rate']}}</td>  
                                   <td style="border-right: 2px #87888a solid;">{{$productivity['c_call_rate']}}</td>  
                                   {{-- <td style="border-right: 2px #87888a solid;">{{$productivity['diff']}}</td>   --}}
                              </tr>
                         @endforeach  
                    </tbody>  
               </table> 
          </div> 
     </div>
    </body>
  </html>