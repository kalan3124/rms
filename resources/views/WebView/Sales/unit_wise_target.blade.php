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
                              <h3 class="center-align report-header">Unit wise Target vs Achivement </h3>
                         </div>
                         <form method="POST" id="form"  style="position: relative" class="card-action">
                              @csrf
                              <div class="row">
                                   <div class="col s12 m2">&nbsp;</div> 
                                   <div style="text-align: center" class="col s12 m3">MONTH</div>
                                   <div class="col s2  m1">:</div>
                                   <div class="col s10 m3" id="datepickerSize">
                                   <input name="date_month" id="date_month" value="{{ isset($dateMonth)?$dateMonth:'' }}" type="text" class="datepicker">
                                   </div>
                                   <div class="col m2">&nbsp;</div>
                              </div>
                              <div class="row">
                                   <div class="col m2">&nbsp;</div> 
                                   <div class="col m3">&nbsp;</div>
                                   <div class="col m1">&nbsp;</div>
                                   <div class="col m1">&nbsp;</div>
                                   <div style="margin:auto" class="col s12 m4" ><button type="submit" style="margin: auto;display: block" class="waves-effect waves-light btn">Search</button></div>
                              </div>
                              <center>
                                   <div id="error" style="color:red"></div>
                              </center>
                         </form>
                    </div>
                    </div>
                    <div class="col s2" id="error_row"></div>
               </div>
          </div>
     </div> 
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
     <div id="root">
     </div>
     <div style="display: none" id="loading">
          <div style="text-align:center">
          <div class="preloader-wrapper big active">
               <div class="spinner-layer spinner-blue-only">
               <div class="circle-clipper left">
               <div class="circle"></div>
               </div><div class="gap-patch">
               <div class="circle"></div>
               </div><div class="circle-clipper right">
               <div class="circle"></div>
               </div>
               </div>
          </div>
          </div>
     </div>
     <script type="text/javascript">

          $('.datepicker').datepicker({
              format: 'yyyy-mm',
          });
        
          $(document).ready(function(){
            
              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
              });

              $('#form').submit(function(e){
                  e.preventDefault();
                  $date = $('#date_month').val();
                  $("#root").html('');
                  $("#error").html('');
                  // $("#error_row").html('');
                  if($date){
                    $('#loading').show();
                    $.ajax({
                      type:'GET',
                      url:'{{ url('/webView/sales') }}/unit_wise_target_search',
                      data:{
                          'token':'<?php echo $_REQUEST['token']; ?>',
                          'date_month':$('#date_month').val(),
                      },
                      success:function(data) {
                          $('#loading').hide();
                          $("#root").html(data);
                      },
                      error:function(data){
                        $('#loading').hide();
                        $('#error').append('<span>Data not found for month</span>');
                      }
                    });
                  } else {
                    $('#error').append('<span>Date field is empty!!!</span>');
                  }
              
              });
          }); 
      </script>
    </body>
  </html>