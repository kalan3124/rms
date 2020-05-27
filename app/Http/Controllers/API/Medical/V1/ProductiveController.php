<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\MediAPIException;
use Illuminate\Support\Facades\DB;
use App\Models\ProductiveVisit;
use App\Models\ProductiveSampleDetails;
use App\Models\UnproductiveVisit;
use App\Models\Doctor;
use App\Models\Chemist;
use App\Models\OtherHospitalStaff;
use App\Models\Notification;
use App\Models\TeamUser;
use App\Models\Product;

class ProductiveController extends Controller{

    public function save(Request $request){

        Storage::put('/public/productives.txt', json_encode($request->all()));
        // Checking the request is empty
        if(!$request->has('jsonString'))
            throw new MediAPIException('Some parameters are not found', 5);

        // Decoding the json
        $json_decode = json_decode($request['jsonString'], true);

        // Getting the logged user
        $user = Auth::user();
        //get userType
        $repType = null;

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            $repType = 'MR';
        } else if(config('shl.field_manager_type')==$user->u_tp_id){
            $repType = 'FM';
        }

        //generate productive no
        $productive = ProductiveVisit::where('u_id', $user->getKey())
            ->count();
        if(!empty($productive)){
            $productive = $productive + 1;
            $proNumber = 'PRO/'.$repType.'/'.$user->id.'/'.$productive;
        } else{
            $proNumber = 'PRO/'.$repType.'/'.$user->id.'/1';
        }
        $ckUnNumber = ProductiveVisit::where('pro_visit_no', $proNumber)
            ->get();
        if(!$ckUnNumber->isEmpty()) throw new MediAPIException("Productive Number Already Exist!",14);

        // Java Timestamp = PHP Unix Timestamp * 1000
        $start_timestamp = $json_decode['start_time'] / 1000;
        $end_timestamp = $json_decode['end_time'] / 1000;

        // Formating the unix timestamp to a string
        $start_time = date("Y-m-d H:i:s", $start_timestamp);
        $end_time = date("Y-m-d H:i:s", $end_timestamp);

        //checking visit type 0-Doctor, 1-Chemist , 2 - other hospital staff
        $chemist = null;
        $doctor = null;
        $otherHospitalStaff = null;
        $promotion = null;
        $second_user = null;
        $name = "DELETED";
        $type = "";

        if($json_decode['visit_type_id']== 0){
            $doctor = $json_decode['doc_chem_id'];
            $doctor = Doctor::find($json_decode['doc_chem_id']);
            if($doctor){
                $name = $doctor->doc_name;
            }
            $type="doctor";
        }else if($json_decode['visit_type_id']== 1){
            $chemist = $json_decode['doc_chem_id'];
            $chemist = Chemist::find($json_decode['doc_chem_id']);
            if($chemist){
                $name = $chemist->chemist_name;
            }
            $type="chemist";
        } else {
            $otherHospitalStaff = $json_decode['doc_chem_id'];
            $otherHospitalStaff = OtherHospitalStaff::find($otherHospitalStaff);
            if($otherHospitalStaff){
                $name = $otherHospitalStaff->hos_stf_name;
            }
            $type="other hospital staff";
        }

        if($json_decode['promotion_id']!=0)
            $promotion = $json_decode['promotion_id'];

        if($json_decode['joint_field_id']!=0)
            $second_user = $json_decode['joint_field_id'];

        // Getting the last attendance record related to user
        $data = [
            'u_id'=> $user->getKey(),
            'pro_start_time'=> $start_time,
            'pro_end_time'=> $end_time
        ];
        $ck_productivity = ProductiveVisit::where($data)
                    ->latest()
                    ->first();
        if($ck_productivity){
            throw new MediAPIException('Productive information has been already added', 15);
        }else{

            try{
                DB::beginTransaction();

                $sound_name = str_replace(' ','_',microtime());
                $sound_url = date('Y') . '/' . date('m') . '/' . date('d') . '/' . $sound_name;
                if(isset($json_decode['soundclip']))
                    Storage::put('/public/sounds'.'/'.date('Y').'/'.date('m') . '/' . date('d').'/'.$sound_name.'.mp3', base64_decode($json_decode['soundclip']));
                else $sound_url=null;

                $productiveVisit = ProductiveVisit::create([
                    'pro_visit_no'=>$proNumber,
                    'doc_id'=>$doctor?$doctor->getKey():null,
                    'chemist_id'=>$chemist?$chemist->getKey():null,
                    'hos_stf_id'=>$otherHospitalStaff?$otherHospitalStaff->getKey():null,
                    'u_id'=>$user->getKey(),
                    'visit_type'=>$json_decode['visit_type_id'],
                    'is_shedule'=>$json_decode['is_sheduled'],
                    'shedule_id'=>$json_decode['shedule_id'],
                    'audio_path'=>$sound_url,
                    'promo_id'=>$promotion,
                    'promo_remark'=>$json_decode['promotion_remark'],
                    'pro_summary'=>$json_decode['productive_summary'],
                    'join_field_id'=>$second_user,
                    'pro_start_time'=>$start_time,
                    'pro_end_time'=>$end_time,
                    'lat'=>$json_decode['lat'],
                    'lon'=>$json_decode['lon'],
                    'btry_lvl'=>$json_decode['battery_level'],
                    'visited_place'=>$json_decode['visit_place_id'],
                    'app_version'=>$appVersion
                ]);

                $productive_id = $productiveVisit->getKey();

                $productNames = [];

                foreach($json_decode['product_samples'] AS $sample){
                    $product = Product::find($sample['productId']);

                    if($product){
                        $productNames[] = $product->product_name;
                    }

                    ProductiveSampleDetails::create([
                        'pro_visit_id'=>$productive_id,
                        'product_id'=>$sample['productId'],
                        'sampling_reason_id'=>$sample['samplingReasonId']!=0?$sample['samplingReasonId']:null,
                        'detailing_reason_id'=>$sample['detailing_reason_id']!=0?$sample['detailing_reason_id']:null,
                        'promotion_reason_id'=>$sample['promotion_reason_id']!=0?$sample['promotion_reason_id']:null,
                        'qty'=>$sample['qty'],
                        'remark'=>$sample['remark']
                    ]);
                }

                $teamUser = TeamUser::with(['team','team.user'])->where('u_id',$user->getKey())->latest()->first();

                if($teamUser&&$teamUser->team&&$teamUser->team->user){
                    Notification::create([
                        'n_title'=>ucfirst($user->name).' has visited to '.$name.' at '.date('Y-m-d H:i:s',$start_timestamp),
                        'n_content'=>ucfirst($user->name)." has visited to  $name at ".date('Y-m-d H:i:s',$start_timestamp).'. <br/>Start time:- '.date('Y-m-d H:i:s',$start_timestamp).' <br/>End time:- '.date('Y-m-d H:i:s',$start_timestamp).' <br/> Products:-'.implode(',',$productNames),
                        'u_id'=>$teamUser->team->user->getKey(),
                        'n_created_u_id'=>$user->getKey(),
                        'n_type'=>3,
                        'n_ref_id'=>$productive_id
                    ]);
                }
            
                DB::commit();
                return [
                    'result'=>true,
                    "message" => "productive information has been successfully added"
                ];
            }catch(\Exception $e){ 
                DB::rollback();
                throw $e;
                throw new MediAPIException('Productive information has not been added', 16);
            }
        }
    } 

    public function GetPreviousVisits(Request $request){
        // Getting the logged user
        $user = Auth::user();

        $current_date = date("Y-m-d");

        $data = [
            'u_id'=>$user->getKey()
        ];

        $timestamp = $request->input('timestamp');
        if($timestamp)
            $timestamp = $timestamp/1000;

        $ProductiveVisitQuery = ProductiveVisit::query()->where($data)->where('pro_start_time','LIKE','%'.$current_date.'%');

        if($timestamp){
            $ProductiveVisit = $ProductiveVisitQuery->where('pro_start_time','>=',date('Y-m-d H:i:s',$timestamp))->get();
        } else {
            $ProductiveVisit = $ProductiveVisitQuery->get();
        }
        
        $ProductiveVisit->transform(function($pro){
            if($pro->visit_type == 0){
                $doc_chem_id = $pro->doc_id;
            } elseif($pro->visit_type == 1){
                $doc_chem_id = $pro->chemist_id;
            }else{
                $doc_chem_id = $pro->hos_stf_id;
            }
            return [
                'is_productive'=>1, //productive visit
                'itinerary_id'=>$pro->shedule_id,
                'visit_type'=>$pro->visit_type,
                'doc_chem_id'=>$doc_chem_id
            ];
        });

        $UnproductiveVisitQuery = UnproductiveVisit::query()->where($data)->where('unpro_time','LIKE','%'.$current_date.'%');

        if($timestamp){
            $UnproductiveVisit = $UnproductiveVisitQuery->where('unpro_time','>=',date('Y-m-d H:i:s',$timestamp))->get();
        } else {
            $UnproductiveVisit = $UnproductiveVisitQuery->get();
        }

        $UnproductiveVisit->transform(function($unpro){
            if($unpro->visit_type == 0){
                $doc_chem_id = $unpro->doc_id;
            } elseif($unpro->visit_type == 1){
                $doc_chem_id = $unpro->chemist_id;
            }else{
                $doc_chem_id = $unpro->hos_stf_id;
            }
            return [
                'is_productive'=>0, //unproductive visit
                'itinerary_id'=>$unpro->shedule_id,
                'visit_type'=>$unpro->visit_type,
                'doc_chem_id'=>$doc_chem_id
            ];
        });

        // merge other hospital staff with chemist and doctors
        $visit = [];
        $visit = $ProductiveVisit->concat($UnproductiveVisit);

        if($visit->isEmpty()){
            throw new MediAPIException("You have no latest data.",38);
        }

        return [
            "result"=>true,
            "visit_details"=>$visit
        ];
    }
}