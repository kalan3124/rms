@extends('emails.layout')

@section('content')
<div class="container">
    Dear All,<br/><br/>

    <b>{{$loggedUser->name}} [{{ $loggedUser->u_code }}]</b> has approved <b>{{$user->name}} [{{ $user->u_code }}]</b>'s itinerary for <b>{{$month}}</b> month!<br/><br/>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Station</th>
                <th>Day Types</th>
                <th>Mileage</th>
                <th>Towns</th>
            </tr>
        </thead>
        <tbody>
            @forEach($dates as $details)
            <tr>
                <td>{{$details['date']}}</td>
                <td>{{ isset($details['description'])?$details['description']:'N/A' }}</td>
                <td>{{ isset($details['bataType'])?$details['bataType']:'N/A' }}</td>
                <td> @if(isset($details['dayTypes'])) @forEach($details['dayTypes'] as $dayType)
                        <span class="day-type">{{$dayType['label']}}</span>
                    @endForeach @endIf
                </td>
                <td>{{ isset($details['mileage'])?$details['mileage']:'N/A' }}</td>
                <td>{{ implode(', ',isset($details['towns'])?$details['towns']:[]) }}</td>
            </tr>
            @endForeach
        </tbody>
    </table>
</div>
@endsection
