<html>

<head>
    <style>
        @page {
            margin: 0 24px;
        }
    </style>
</head>

<body>
    <div>
        <ul>
            <li>Recipt No : {{$recipt_no}}</li>
            <li>Date : {{$date}}</li>
            <li>Customer : {{$customer}}</li>
            <li>Payment Method : {{$p_type}}</li>
            <li>Amount : {{$amount}}</li>
            <li>User : {{$printed_user}}</li>
            <li>{{$original?'Original':'Copy'}}</li>
            <li>Invoices : {{$remarks}}</li>
        </ul>
    </div>
</body>

</html>
