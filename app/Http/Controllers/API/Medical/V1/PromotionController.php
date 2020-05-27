<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator; 
use App\Models\Promotion;
use App\Models\DoctorPromotionByMr;
use App\Models\DoctorPromotionDetailsByMr;

class PromotionController extends Controller{

    public function save(Request $request){
        $user= Auth::user();

        if(!$request->has('jsonString'))
            throw new MediAPIException('Some parameters are not found', 5);

        // Decoding the json
        $json_decode = json_decode($request['jsonString'],true);
        // Make a new validation rule
        $validator = Validator::make($json_decode, [
            'doctorId' => 'required',
            'promotionId' => 'required',
            'unitId' => 'required',
            'headCount' => 'required',
            'totalValue' => 'required',
            'promo_time' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'battery_level' => 'required',
            'promo_products' => 'required'
        ]);
        
        // Throw an exception if required parameters not supplied
        if ($validator->fails()) 
            throw new MediAPIException($validator->errors()->first(), 4);
        
        // Java Timestamp = PHP Unix Timestamp * 1000
        $timestamp = $json_decode['promo_time'] / 1000;

        // Formating the unix timestamp to a string
        $promo_time = date("Y-m-d h:i:s", $timestamp);

        $imgUrl = null;
        if(isset($json_decode['image_file'])){
        // Bese 64 image conversion
        $image = $json_decode['image_file'];
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = str_random(10).'.'.'png';
        Storage::put('/public/doctorPromoImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName,base64_decode($image));

        $imgUrl = 'storage/doctorPromoImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName;
        }
        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        $data = [
            'u_id'=> $user->getKey(),
            'doc_id'=>$json_decode['doctorId'],
            'promo_id'=>$json_decode['promotionId'],
            'vt_id'=>$json_decode['unitId'],
            'promo_date'=> $promo_time
        ];
        $ck_doctorPromo = DoctorPromotionByMr::where($data)
        ->latest()
        ->first();
        if($ck_doctorPromo){
            throw new MediAPIException('Doctor promotion information has been already added', 27);
        }else{
        try{
            DB::beginTransaction();
        $docPromo = DoctorPromotionByMr::create([
            'u_id'=> $user->getKey(),
            'doc_id'=>$json_decode['doctorId'],
            'promo_id'=>$json_decode['promotionId'],
            'vt_id'=>$json_decode['unitId'],
            'head_count'=>$json_decode['headCount'],
            'promo_value'=>$json_decode['totalValue'],
            'promo_date'=>$promo_time,
            'promo_lat'=>$json_decode['lat'],
            'promo_lon'=>$json_decode['lon'],
            'bat_lvl'=>$json_decode['battery_level'],
            'image_url'=>$imgUrl,
            'app_version'=>$appVersion
        ]);
        $docPromo_id = $docPromo->getKey();
        
        foreach ($json_decode['promo_products'] AS $pp){
            $docPromoProduct = DoctorPromotionDetailsByMr::create([
                'dpbmr_id'=>$docPromo_id,
                'product_id'=>$pp['promo_item_id']
            ]);
        }

        DB::commit();
        return [
            'result'=>true,
            "message" => "Doctor promotion information has been successfully added"
        ];
        }catch(\Exception $e){ 
            DB::rollback();
            throw new MediAPIException('Doctor Promotion information has not been added', 26);
        }
        }
    }


    public function index(){

        $promotions = Promotion::whereDate('start_date','<=',date("Y-m-d"))->whereDate('end_date','>=',date("Y-m-d"))->get();

        $promotions->transform(function($promotion){
            return [
                'promo_id'=>$promotion->getKey(),
                'promo_name'=>$promotion->promo_name
            ];
        });

        return [
            'result'=>true,
            'promotion'=>$promotions,
            'count'=>$promotions->count()
        ];
    }
}