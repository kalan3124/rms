<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Validator;
use App\Models\ItineraryDateChange;
use App\Models\Team;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use App\Exceptions\WebAPIException;
use App\Models\ItineraryDate;
use App\Models\UserItinerarySubTown;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;

class ItineraryChangesController extends ReportController {

    protected $title = "Itinerary Changes Approval Report";

    public function search(Request $request){
        $team = $request->input('values.team.value');
        $division = $request->input('values.division.value');

        $user = $request->input('values.user.value');
        $date = $request->input('values.date');

        $userIds = null;

        if(isset($team)&&!isset($user)){
            $team = Team::with(['teamUsers'])->find($team);

            $userIds = $team->teamUsers->pluck('u_id');
            $userIds->push($team->fm_id);
        }

        if(isset($user)) {
            $userIds = collect([$user]);
        }


        $query = ItineraryDateChange::query();

        if(isset($userIds)){
            $query->whereIn('u_id',$userIds->values());
        }
        
        if(isset($date)){
            $query->whereDate('idc_date',$date);
        }

        if(isset($division)){
            $users = User::where('divi_id',$division)->get();

            $query->whereIn('u_id',$users->pluck('id')->all());
        }

        $count = $this->paginateAndCount($query,$request,'idc_date');
        
        $query->with(['user','user.teamUser','user.teamUser.team','bataType','approver','areas','areas.subTown']);

        $results = $query->get();

        $results->transform(function($row){

            $row->areas->transform(function($area){
                if(!isset($area->subTown))
                    return null;

                return [
                    'value'=>$area->subTown->sub_twn_id,
                    'label'=>$area->subTown->sub_twn_name
                ];
            });

            return [
                'u_id'=>$row->user?[
                    'label'=>$row->user->name,
                    'value'=>$row->user->id
                ]:null,
                'tm_id'=>$row->user&&$row->user->teamUser&&$row->user->teamUser->team?[
                    'label'=>$row->user->teamUser->team->tm_name,
                    'value'=>$row->user->teamUser->team->tm_id
                ]:null,
                'bt_id'=>$row->bataType?[
                    'label'=>$row->bataType->bt_name,
                    'value'=>$row->bataType->bt_id
                ]:null,
                'idc_aprvd_u_id'=>$row->approver?[
                    'label'=>$row->approver->name,
                    'value'=>$row->approver->id
                ]:null,
                'idc_mileage'=>$row->idc_mileage,
                'idc_date'=>$row->idc_date,
                'idc_aprvd_at'=>$row->idc_aprvd_at,
                'created_at'=>$row->created_at->format("Y-m-d"),
                'aprv_btn'=>$row->idc_aprvd_at?null:$row->idc_id,
                'areas'=>$row->areas->filter(function($area){return !!$area;})->values()
            ];
        });

        return [
            'results'=>$results,
            'count'=>$count
        ];
    }

    protected function setColumns(ColumnController $columnController,Request $request){
        $columnController->ajax_dropdown('tm_id')->setLabel("Team Name");
        $columnController->ajax_dropdown('u_id')->setLabel("User");
        $columnController->ajax_dropdown('bt_id')->setLabel("Bata Type");
        $columnController->text('idc_mileage')->setLabel("Mileage");
        $columnController->multiple_ajax_dropdown('areas')->setLabel("Sub Towns")->setSearchable();
        $columnController->text('idc_date')->setLabel("Date");
        $columnController->ajax_dropdown('idc_aprvd_u_id')->setLabel("Approver");
        $columnController->text('idc_aprvd_at')->setLabel("Approved Date");
        $columnController->text('created_at')->setLabel("Created Date");
        $columnController->button('aprv_btn')->setLabel('Approve')->setLink("report/itinerary_change/approve")->setSearchable();
    }

    protected function setInputs($inputController){
         $inputController->ajax_dropdown("division")->setLabel("Division")->setLink("division");
         $inputController->ajax_dropdown("team")->setLabel("Team")->setLink("team")->setWhere(['divi_id'=>'{division}']);
         $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team}","divi_id" => "{division}"])->setLabel('MR/PS or FM')->setLink('user');
         $inputController->date("date")->setLabel("Date");

         $inputController->setStructure([
              ["division","team"],
              ["user","date"]
         ]);
    }

    public function approve(Request $request){
        $validation = Validator::make($request->all(),[
            'value'=>'required|numeric|exists:itinerary_date_changes,idc_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please try after refresh your browser.");
        }

        $user = Auth::user();

        try{

            DB::beginTransaction();

            $itineraryChangedDate = ItineraryDateChange::with(['areas','user'])->where('idc_id',$request->input('value'))->first();

            if($itineraryChangedDate->idc_aprvd_u_id)
                throw new WebAPIException("Already approved your itinerary date!");

            $itineraryChangedDate->update([
                'idc_aprvd_u_id'=>$user->getKey(),
                'idc_aprvd_at'=>date("Y-m-d H:i:s")
            ]);
    
            $timestamp = strtotime($itineraryChangedDate->idc_date);
    
            $itineraryDate = ItineraryDate::getTodayForUser($itineraryChangedDate->user,[],$timestamp,true);
            $itineraryDate->idc_id = $itineraryChangedDate->getKey();
            $itineraryDate->update();

            UserItinerarySubTown::where('id_id',$itineraryDate->getKey())->delete();

            $areas = [];
            foreach ($itineraryChangedDate->areas as $area) {
                $areas[] = [
                    "u_id"=>$itineraryChangedDate->u_id,
                    "sub_twn_id"=>$area->sub_twn_id,
                    "i_id"=>$itineraryDate->i_id,
                    "id_id"=>$itineraryDate->id_id,
                    "uist_year"=>date('Y',$timestamp),
                    "uist_month"=>date('m',$timestamp),
                    "uist_date"=>date('d',$timestamp),
                    "uist_approved"=>1,
                    'idc_id'=>$itineraryChangedDate->idc_id
                ];
            }
    
            UserItinerarySubTown::insert($areas);

            Notification::create([
                'n_title'=>"Your itinerary has been approved.",
                'n_content'=>"The itinerary that you changed on <b>".$itineraryChangedDate->idc_date."</b> at <b>".$itineraryChangedDate->created_at->format('H:i:s')."</b> is approved by <b>".$user->name."</b> from <b>".date('Y-m-d H:i:s')."</b>. Please refresh your app to take effect.",
                'u_id'=>$itineraryChangedDate->u_id,
                'n_created_u_id'=>$user->getKey(),
                'n_type'=>2
            ]);

            DB::commit();
    
        } catch(\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'message'=>"Successfully approved your itinerary date and sent a notification to MR/PS.",
            "success"=>true
        ]);
    }

}