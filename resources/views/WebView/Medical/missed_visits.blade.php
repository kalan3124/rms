<!DOCTYPE html>
  <html>
    <head>
      <!--Import Google Icon Font-->
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
      <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <link href="{{ url('/css/style.css')}}" rel="stylesheet" type="text/css">
      <link href="{{ url('/css/WebView/style.css')}}" rel="stylesheet" type="text/css">
      <style type="text/css">
        .image-letter{
            height: 50px!important;
            width: 50px!important;
            color: #fff!important;
            font-size: 2em!important;
            text-align: center!important;
            line-height: 40px!important;
            border-radius: 12px!important;
        }
        .card-title{
            padding-left:70px;
        }
      </style>
    </head>
    <body>
        <div class="wrapper">
        <div class="row">
          <div class="col s12">
            <div class = "card-panel center">
              <h3>Missed Visits</h3>       
            </div>
            
            <div id="missed_visit">
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
            
            
          </div>
        </div>
        </div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
              $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
              $.ajax({
               type:'GET',
               url:'{{ url('/webView/medical') }}/missedVisitSearch',
               data:{
                  'token':'<?php echo $_REQUEST['token']; ?>',
                  'fdate':'<?php echo $_REQUEST['fdate']; ?>',
                  'tdate':'<?php echo $_REQUEST['tdate']; ?>'
               },
               success:function(data) {
                  $("#missed_visit").html(data);
               }
            });
            }); 
        </script>
    </body>
  </html>