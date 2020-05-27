<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

use App\Models\DoctorTimeTable;
use App\Models\DoctorTimeTableTime;
use Validator;
use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;

class DoctorTimeTableController extends Controller
{
    public function load(Request $request){
        $doctor = $request->input('doc');

        $docId = $doctor['value'];

        $timeTable = DoctorTimeTable::where('doc_id',$docId)->with(['doctorTimeTableTimes','doctorTimeTableTimes.institution'])->latest()->first();

        if(!$timeTable) abort(404);

        $shedules = [];

        foreach($timeTable->doctorTimeTableTimes as $key=>$shedule){
            if(!isset($shedules[$shedule->getDayName()])) $shedules[$shedule->getDayName()] = [];

            $shedules[$shedule->getDayName()][] = [
                'id'=>$key,
                'name'=>$shedule->institution->ins_name,
                'value'=>$shedule->institution->getKey(),
                'startTime'=>$shedule->dttt_s_time,
                'endTime'=>$shedule->dttt_e_time,
                'type'=>'custom',
                'dayName'=>$shedule->getDayName()
            ];
        }

        return [
            'shedules'=>$shedules,
            'count'=>$key
        ];
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(),[
            'doc.value'=>'required|numeric|exists:doctors,doc_id',
            'shedules'=>'array',
            'shedules.*'=>'array',
            'shedules.*.*.value'=>'numeric|exists:institutions,ins_id',
            'shedules.*.*.startTime'=>'sometimes|date',
            'shedules.*.*.endTime'=>'sometimes|date'
        ]);

        if($validator->fails()){
            throw new WebAPIException($validator->errors()->first());
        }

        $doctor = $request->input('doc');

        $doctorId = $doctor['value'];

        $shedules = $request->input('shedules');

        $timeTable = DoctorTimeTable::create([
            'doc_id'=>$doctorId
        ]);

        foreach ($shedules as $dayName => $day) {

            foreach($day as $shedule){
                DoctorTimeTableTime::create([
                    'dtt_id'=>$timeTable->getKey(),
                    'dttt_week_day'=>DoctorTimeTableTime::getDayId($shedule['dayName']),
                    "ins_id"=>$shedule['value'],
                    "dttt_s_time"=>date('H:i:s',strtotime($shedule['startTime'])),
                    "dttt_e_time"=>date('H:i:s',strtotime($shedule['endTime'])),
                ]);
            }
        }

        return [
            "message"=>"Successfully saved the time table fot ".$doctor['label']
        ];
    }

}
