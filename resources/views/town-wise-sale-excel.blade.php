<table>
     <thead>
         <tr>
             <th style="text-align:center" colspan="6">
                 <b>
                     Town Wise Sale Report
                 </b>
             </th>
         </tr>
         <tr>
               <th colspan="6">

               </th>
          </tr>
          @if(count($searchTerms))
            <tr>
                <td style="text-align:center" colspan="4" >
                    <b>Searched Terms</b>
                </td>
                <td colspan="{{ 6-4 }}">
                </td>
            </tr>
            @foreach ($searchTerms as $searchTerm)
                <tr>
                    <td colspan="2" >
                        {{$searchTerm['label']}} :-
                    </td>
                    <td colspan="2">
                        {{$searchTerm['value']}}
                    </td>
                    <td colspan="{{ 6-4 }}">
                    
                    </td>
                </tr>
            @endforeach
        @endIf
        <tr>
            <th colspan="6">

            </th>
        </tr>
     </thead>
     <tbody>
     </tbody>
 </table>
 <table>
    <thead>
        <tr>
            <th style="text-align:center" colspan="6">
                <b>
                    Route Wise Achievement
                </b>
            </th>
        </tr>
        <tr>
            <th align="left" style="background-color:#757575">Route</th>
            <th align="left" style="background-color:#757575">Target</th>
            <th align="left" style="background-color:#757575">Achievemant</th>
            <th align="left" style="background-color:#757575">%</th>
            <th align="left" style="background-color:#757575">Balance</th>
            <th align="left" style="background-color:#757575">Contribution</th>
        </tr>
    </thead>
    <tbody>
        {{-- @isset($results)
            
        @endisset --}}
        @foreach ($results1 as $result )
            <tr>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['route']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['target']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['achi']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">
                    @isset($result['ach_pra'])
                    {{$result['ach_pra']}}
                    @endisset
                </td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['balance']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">0</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table>
    <thead>
        <tr>
            <th style="text-align:center" colspan="6">
                <b>
                    Town Wise Achievement
                </b>
            </th>
        </tr>
        <tr>
            <th align="left" style="background-color:#757575">Town</th>
            <th align="left" style="background-color:#757575">Target</th>
            <th align="left" style="background-color:#757575">Achievemant</th>
            <th align="left" style="background-color:#757575">%</th>
            <th align="left" style="background-color:#757575">Balance</th>
            <th align="left" style="background-color:#757575">Contribution</th>
        </tr>
    </thead>
    <tbody>
        {{-- @isset($results)
            
        @endisset --}}
        @foreach ($results2 as $result )
            <tr>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['town_name']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['target']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['achi']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">
                    @isset($result['ach_pra'])
                    {{$result['ach_pra']}}
                    @endisset
                </td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['balance']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">0</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table>
    <thead>
        <tr>
            <th style="text-align:center" colspan="6">
                <b>
                    Customer Wise Achievement
                </b>
            </th>
        </tr>
        <tr>
            <th align="left" style="background-color:#757575">Customer</th>
            <th align="left" style="background-color:#757575">Customer Name</th>
            <th align="left" style="background-color:#757575">Target</th>
            <th align="left" style="background-color:#757575">Achievemant</th>
            <th align="left" style="background-color:#757575">%</th>
            <th align="left" style="background-color:#757575">Balance</th>
            <th align="left" style="background-color:#757575">Contribution</th>
        </tr>
    </thead>
    <tbody>
        {{-- @isset($results)
            
        @endisset --}}
        @foreach ($results3 as $result )
            <tr>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['chemist']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['chemist_name']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['target']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['achi']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">
                    @isset($result['precentage'])
                    {{$result['precentage']}}
                    @endisset
                </td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">{{$result['balance']}}</td>
                <td style="@isset($result['special']) background:#64d6ed @endisset">0</td>
            </tr>
        @endforeach
    </tbody>
</table>
 <table>
    <thead>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align: center;color:#ffffff; background:#000000">
                    © 2019 🐧 Ceylon Linux (PVT) LTD| All Rights Reserved.
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <a href="{{ $link }}">Download the original document.</a>
            </td>
        </tr>
    </tfoot>
</table>

 