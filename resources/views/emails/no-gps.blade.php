@extends('emails.layout')

@section('content')
<div class="container">
    Dear All,<br/><br/>

    GPS not received for below users. Please inform them to turn on GPS service.<br/>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Contact Number</th>
                <th>Last Location (Latitude, Longitude)</th>
                <th>Last GPS Time</th>
                <th>Last Battery Level</th>
            </tr>
        </thead>
        <tbody>
            @forEach($users as $user)
            <tr>
                <td>{{$user['name']}}</td>
                <td>{{ $user['code'] }}</td>
                <td>{{ $user['mobile'] }}</td>
                <td>{{ isset($user['last_location'])? round($user['last_location']['lat'],3).', '.round($user['last_location']['lng'],3): 'N/A' }}</td>
                <td>{{ isset($user['last_location'])? $user['last_location']['time']: 'N/A' }}</td>
                <td>{{ isset($user['last_location'])? $user['last_location']['batry']: 'N/A' }}</td>
            </tr>
            @endForeach
        </tbody>
    </table>
</div>
@endsection
