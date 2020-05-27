<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <title>Document</title>
     <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

     <style>
          #doctors {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
          }
          
          #doctors td, #doctors th {
            border: 1px solid #ddd;
            padding: 8px;
          }
          
          #doctors tr:nth-child(even){background-color: #f2f2f2;}
          
          #doctors tr:hover {background-color: #ddd;}
          
          #doctors th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #33BEFF;
            color: white;
          }
     
     </style>
</head>
<body>
     <div class="container"><br>
          <table id="doctors">
               <tr>
                    <th>Doctor</th>
                    <th>Date</th>
               </tr>
               @foreach ($doc_vists as $doc_vist)
                    <tr>
                         <td>{{$doc_vist->doc_name}}</td>
                         <td>{{$doc_vist->pro_start_time}}</td>
                    </tr>
               @endforeach
          </table>
          <center><span style="color:red">{{$error}}</span></center>
     </div>
</body>
</html>

<script>

     // $(function() {
          
     //        var tr = $('#doctors').find('tr');
     //        tr.bind('click', function(event) {
     //            var values = '';
     //            tr.removeClass('row-highlight');
     //            var tds = $(this).addClass('row-highlight').find('td');
                

     //            $.each(tds, function(index, item) {
     //                values =values + 'td' + (index + 1) + ':' +  '<br/>';//item.innerHTML;
     //            });
     //            alert(values);
     //        });
     // });
</script>