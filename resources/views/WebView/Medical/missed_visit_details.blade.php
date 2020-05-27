<div>
@if (sizeof($result) > 0)
@foreach ($result AS $data)
    @if (sizeof($data['missedVisits']) > 0)
    <ul class="collection">
        <li class="collection-item">Date : {{$data['date']}}</li>
    </ul>
    
    <ul class="collection">
    @foreach ($data['missedVisits'] AS $missed)
        <li class="collection-item avatar">
        <i class="material-icons circle red image-letter">
        @switch($missed['doc_chem_type'])
        @case(0)
            D
            @break
        @case(1)
            C
            @break
        @default
            O
    @endswitch
        </i>
        <span class="title">{{$missed['doc_chem_name']}}</span>
        <p style="padding-top:5px">{{$missed['speciality']}}</p>
        </li>
        @endforeach
    </ul>
    @endif
@endforeach
@else
<ul class="collection">
    <li class="collection-item" style="text-align:center;color:red">No Records Found</li>
</ul>
@endif
</div>
