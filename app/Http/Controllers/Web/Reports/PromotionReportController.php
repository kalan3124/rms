<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Models\DoctorPromotionByMr;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Support\Facades\Auth;

class PromotionReportController extends ReportController{
    protected $title = "Promotions Report";

    public function search(Request $request){

        $values = $request->input('values');
        $page = $request->input('page')??1;
        $perPage = $request->input('perPage')??25;
        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'user':
                $sortBy = 'u_id';
                break;
            case 'doctor':
                $sortBy = 'doc_id';
                break;
            case 'promotion':
                $sortBy = 'promo_id';
                break;
            case 'visit_place':
                $sortBy = 'vt_id';
                break;
            case 'head_count':
                $sortBy = 'head_count';
                break;
            case 'promo_value':
                $sortBy = 'promo_value';
                break;
            case 'app_version':
                $sortBy = 'app_version';
                break;
            default:
                $sortBy = 'promo_date';
                break;
        }

        // $query = DoctorPromotionByMr::query();
        $query = DoctorPromotionByMr::from('doctor_promotion_by_mr')
        ->select(['doctor_promotion_by_mr.*','u.divi_id'])
        ->join('users AS u','doctor_promotion_by_mr.u_id','u.id');

        if(isset($values['divi_id'])){
            $query->where('u.divi_id',$values['divi_id']['value']);
        }

        if(isset($values['s_date'])&&isset($values['e_date'])){
            $query->whereDate('doctor_promotion_by_mr.promo_date',">=",date("Y-m-d",strtotime($values['s_date'])));
            $query->whereDate('doctor_promotion_by_mr.promo_date',"<=",date("Y-m-d",strtotime($values['e_date'])));
        }

        if(isset($values['user'])){
            $query->where('doctor_promotion_by_mr.u_id',$values['user']['value']);
        } else {
            $user = Auth::user();
            /** @var \App\Models\User $user */
    
            if(in_array($user->getRoll(),[
                config('shl.product_specialist_type'),
                config('shl.medical_rep_type'),
                config('shl.field_manager_type')
            ])){
                $users = User::getByUser($user);
                $query->whereIn('doctor_promotion_by_mr.u_id',$users->pluck('u_id')->all());
            }


            $teams = UserTeam::where('u_id',$user->getKey())->get();
            if($teams->count()){
                $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
                $query->whereIn('doctor_promotion_by_mr.u_id',$users->pluck('u_id')->all());
            }  
        }

        if(isset($values['doctor'])){
            $query->where('doctor_promotion_by_mr.doc_id',$values['doctor']['value']);
        }

        if(isset($values['promotion'])){
            $query->where('doctor_promotion_by_mr.prmo_id',$values['promotion']['value']);
        }

        if(isset($values['visit_place'])){
            $query->where('doctor_promotion_by_mr.vt_id',$values['visit_place']['value']);
        }

        $query->orderBy($sortBy,$sortMode);

        $count = $query->count();

        $query->take($perPage);

        $query->skip(($page-1)*$perPage);

        $query->with(['user',"doctor","promotion","visit_place","details","details.product"]);

        $results =  $query->get();

        $results->transform(function($promotion){

            $promotion->details->transform(function($detail){
                return [
                    "label"=>$detail->product? $detail->product->product_name:"Deleted!",
                    "value"=>$detail->product? $detail->product->getKey():0
                ];
            });
            
            return [
                'user'=>[
                    'label'=>$promotion->user->name,
                    'value'=>$promotion->user->getKey()
                ],
                "doctor"=>[
                    "value"=>$promotion->doctor->getKey(),
                    "label"=>$promotion->doctor->doc_name
                ],
                "promotion"=>[
                    "value"=>$promotion->promotion->getKey(),
                    "label"=>$promotion->promotion->promo_name
                ],
                "visit_place"=>[
                    "value"=>$promotion->visit_place->getKey(),
                    "label"=>$promotion->visit_place->vt_name,
                ],
                "head_count"=>$promotion->head_count,
                "promo_value"=>$promotion->promo_value??0.00,
                "latitude"=>$promotion->promo_lat,
                "longitude"=>$promotion->promo_lon,
                "app_version"=>$promotion->app_version,
                "promo_time"=>$promotion->promo_date,
                "image"=>$promotion->image_url,
                "products"=>$promotion->details
            ];
        });

        return [
            'count'=>$count,
            'results'=>$results
        ];

    }

    public function setColumns($columnController, Request $request){
        $columnController->ajax_dropdown("user")->setLabel("MR or FM");
        $columnController->ajax_dropdown("doctor")->setLabel("Doctor");
        $columnController->ajax_dropdown("promotion")->setLabel("Promotion");
        $columnController->ajax_dropdown("visit_place")->setLabel("Visit placed");
        $columnController->text("head_count")->setLabel("Person Count");
        $columnController->text("promo_value")->setLabel("Value");
        $columnController->text("latitude")->setLabel("Latitude");
        $columnController->text("longitude")->setLabel("Longitude");
        $columnController->image("image")->setLabel("Image");
        $columnController->multiple_ajax_dropdown("products")->setLabel("Products");
        $columnController->text("app_version")->setLabel("App version");
        $columnController->text("promo_time")->setLabel("Date and Time");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division');
        // $inputController->ajax_dropdown("user")->setLabel("MR or FM")->setLink("user");
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team');
        $inputController->ajax_dropdown("user")->setWhere(['tm_id'=>"{team}",'divi_id'=>"{divi_id}"])->setLabel("MR/PS or FM")->setLink("user");
        $inputController->ajax_dropdown("doctor")->setLabel("Doctor")->setLink("doctor");
        $inputController->ajax_dropdown("promotion")->setLabel("Promotion")->setLink("promotion");
        $inputController->ajax_dropdown("visit_place")->setLabel("Visited Place")->setLink("visit_type");
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([["team","user","doctor","promotion"],["divi_id","visit_place","s_date","e_date"]]);
    }
}