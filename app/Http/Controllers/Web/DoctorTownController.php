<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\Doctor;
use App\Http\Controllers\Controller;
use App\Models\DoctorSubTown;

class DoctorTownController extends Controller {

    public function getTownsByDoctor(Request $request){
        $validation = Validator::make($request->all(),[
            'doctor'=>'required|array',
            'doctor.value'=>'required|numeric|exists:doctors,doc_id'
        ]);

        if ($validation->fails()){
            throw new WebAPIException("Can not find a doctor for your input.");
        }

        $doctor = Doctor::with(['subTowns','subTowns.subTown'])->find($request->input('doctor.value'));

        if($doctor){
            $subTowns = $doctor->subTowns->map(function($area){
                if(!$area->subTown){
                    return null;
                }

                return [
                    'value'=>$area->subTown->getKey(),
                    'label'=>$area->subTown->sub_twn_name
                ];
            });

            $subTowns = $subTowns->filter(function($subTown){
                return !!$subTown;
            });

            return [
                'success'=>true,
                'towns'=>$subTowns
            ];
        }

        throw new WebAPIException("Requested doctor is deactivated.");
        
    }

    public function save(Request $request){
        $validations = Validator::make($request->all(),[
            'doctors'=>'required|array',
            'towns'=>'required|array'
        ]);

        if($validations->fails()){
            throw new WebAPIException("Invalid request sent.");
        }

        $doctors = $request->input('doctors');
        $towns = $request->input('towns');

        try{

            DoctorSubTown::whereIn('doc_id',array_column($doctors,'value'))->delete();

            foreach($doctors as $doctor){
                foreach ($towns as $town) {
                    DoctorSubTown::create([
                        'sub_twn_id'=>$town['value'],
                        'doc_id'=>$doctor['value']
                    ]);
                }
            }

        } catch (\Exception $e){
            throw new WebAPIException("Server error apeared.");
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully allocated towns to doctors"
        ]);

    }

}