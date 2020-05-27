<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\MrDoctorCreaton;
use App\Models\Doctor;
use App\Models\DoctorSubTown;
use App\Models\DoctorInstitution;

class DoctorApproveController extends Controller{
    public function search(Request $request){
        $validation = Validator::make($request->all(),[
            "user"=>"array",
            "user.value"=>"exists:users,id",
            "toDate"=>"date",
            "fromDate"=>"date"
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $user = $request->input("user");
        $toDate = $request->input("toDate",date("Y-m-d"));
        $fromDate = $request->input("fromDate",date("Y-m-d"));

        $query = MrDoctorCreaton::query();

        if($request->has("user")){
            $query->where('u_id',$user['value']);
        }

        $query->whereDate("added_date",">=", date('Y-m-d',strtotime( $fromDate)));
        $query->whereDate("added_date","<=", date('Y-m-d',strtotime($toDate)));

        $query->with(['user',"doctor_speciality","doctor_class","sub_town","institution"]);

        $query->orderBy("created_at");

        $results = $query->get();

        $results->transform(function($docAprv){
            
            $filtered = $docAprv->only([
                'doc_code','doc_name','slmc_no','phone_no','mobile_no','date_of_birth','added_date','app_version'
            ]);

            $filtered['gender'] = [
                'label'=>$docAprv->gender?"Male":"Female",
                'value'=>$docAprv->gender==0?1:2
            ];

            $filtered['id']= $docAprv->getKey();
            $filtered['user'] = null;
            $filtered['doctor_speciality'] = null;
            $filtered['doctor_class'] = null;
            $filtered['sub_town'] = null;
            $filtered['institution'] = null;

            $filteringRelationsNames = [
                'doctor_speciality'=>'speciality_name',
                'user'=>'name',
                'doctor_class'=>'doc_class_name',
                'sub_town'=>'sub_twn_name',
                'institution'=>'ins_name'
            ];

            foreach ($filteringRelationsNames as $relationName => $nameField) {
                $filtered[$relationName] = null;
                if(isset($docAprv->{$relationName})){
                    $filtered[$relationName] = [
                        'label'=>$docAprv->{$relationName}->{$nameField},
                        'value'=>$docAprv->{$relationName}->getKey()
                    ];
                }
            }

            return $filtered;

        });

        return response()->json([
            'success'=>true,
            'results'=>$results->all(),
        ]);

    }

    public function save(Request $request){
        $validation = Validator::make($request->all(),[
            'key'=>'required|exists:mr_doctor_creation,mr_doc_id',
            'values'=>'required|array'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        MrDoctorCreaton::find($request->input('key'))->delete();


        $doctor = Doctor::create([
            'doc_code'=>$request->input('values.doc_code'),
            'doc_name'=>$request->input('values.doc_name'),
            'mobile_no'=>$request->input('values.mobile_no'),
            'phone_no'=>$request->input('values.phone_no'),
            'slmc_no'=>$request->input('values.slmc_no'),
            'doc_class_id'=>$request->input('values.doctor_class.value'),
            'doc_spc_id'=>$request->input('values.doctor_speciality.value'),
            'gender'=>$request->input('values.gender.value'),
            'approved_at'=>date('Y-m-d H:i:s')
        ]);

        DoctorSubTown::create([
            'doc_id'=>$doctor->getKey(),
            'sub_twn_id'=>$request->input('values.sub_town.value'),
        ]);

        DoctorInstitution::create([
            'doc_id'=>$doctor->getKey(),
            'ins_id'=>$request->input('values.institution.value'),
        ]);

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully approved and created the doctor."
        ]);
    }

    public function delete(Request $request){
        $validation = Validator::make($request->all(),[
            'key'=>'required|exists:mr_doctor_creation,mr_doc_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        MrDoctorCreaton::find($request->input('key'))->delete();


        return response()->json([
            'success'=>true,
            'message'=>"You have successfully unapproved the doctor."
        ]);
    }
}