<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\EmailSelectedType;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\SpecialDay;
use App\Models\SubTown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\UserItinerarySubTown;
use App\Models\User;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class ItineraryApprovalController extends Controller{
    /**
     * Searching for approvals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request){

        if(!$request->input('team.value') && !$request->input('division.value')){
            throw new WebAPIException("Invalid request. Make sure you have selected a representaive.");
        }

        $type= $request->input('type',0);

        if($request->input('team.value')){
            $team = Team::with(['teamUsers','teamUsers.user','user'])->where('tm_id',$request->input('team.value'))->first();

            $users = $team->teamUsers->map(function($teamUser){
                return isset($teamUser->user)?$teamUser->user:null;
            });

            $users->push($team->user);

            $users = $users->filter(function($user){
                 return !!$user;
            });

            if($request->input('division.value'))
                $users = $users->where('divi_id',$request->input('division.value'));

        } else if($request->input('division.value')){
            $users = User::where('divi_id',$request->input('division.value'))->get();
        }

        $itineraries = collect([]);

        foreach ($users as  $user) {
            $query = Itinerary::query();

            $query->with('approver');

            $query->where(function($query)use($user){
                $query->orWhere('rep_id',$user->getKey());
                $query->orWhere('fm_id',$user->getKey());
            });

            $query->latest();

            $itinerary = $query->first();

            if($itinerary){
                $itinerary->user = $user;

                if(isset($itinerary->i_aprvd_at)&&$type)
                    $itineraries->push($itinerary);
                else if (!isset($itinerary->i_aprvd_at)&&!$type)
                    $itineraries->push($itinerary);
            }
        }

        $itineraries = $itineraries->filter(function($itinerary){ return !!$itinerary; });

        $itineraries->transform(function($itinerary){
            $teamUser = TeamUser::with('team')->where('u_id',$itinerary->user->getKey())->latest()->first();
            $team = Team::where('fm_id',$itinerary->user->u_id)->latest()->first();
            $year = date('Y');
            $month = date('m');

            if(isset($teamUser)&&$teamUser->team){
                $team = $teamUser->team;
            }

            if(! $team){
                return null;
            }

            if($itinerary->i_year<=$year&&$itinerary->i_month<=$month-2){
                return null;
            }

            if($itinerary->i_year<=$year&&$itinerary->i_month<=$month-1&&strtotime($team->tm_exp_block_date)>=time()){
                return null;
            }

            return [
                'id'=>$itinerary->getKey(),
                'type'=>isset($itinerary->i_aprvd_at)?1:0,
                'approvedTime'=>$itinerary->i_aprvd_at,
                'approvedBy'=>$itinerary->approver?$itinerary->approver->getName():null,
                'yearMonth'=>$itinerary->i_year." - ".str_pad($itinerary->i_month,2,'0',STR_PAD_LEFT),
                'createdTime'=>$itinerary->created_at->format("Y-m-d H:i:s"),
                'user'=>$itinerary->user->name
            ];
        });

        $itineraries=  $itineraries->filter(function($itinerary){
            return !!$itinerary;
        })->values();

        return response()->json([
            'results'=>$itineraries
        ]);
    }
    /**
     * Approving an itinerary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request){
        $validation = Validator::make($request->all(),[
            'id'=>'required|numeric|exists:itinerary,i_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request.");
        }

        $user = Auth::user();

        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            throw new WebAPIException("Forbidden! You haven't permission to approve an itinerary.");
        }

        $itinerary = Itinerary::with(['medicalRep','fieldManager','itineraryDates'])
            ->find($request->input('id'));


        $yearMonth = $itinerary->i_year.'-'.str_pad($itinerary->i_month,2,'0',STR_PAD_LEFT);

        $specialDays = SpecialDay::where('sd_date','LIKE',"$yearMonth%")->get();

        $specialDays->transform(function($specialDay){
            return [
                'date'=>date('d',strtotime($specialDay->sd_date)),
                'title'=>$specialDay->sd_name
            ];
        });

        $workingDayCount = date('t',strtotime($yearMonth.'-01'));

        $begin = new \DateTime( $yearMonth.'-01' );
        $end   = new \DateTime( $yearMonth.'-'.$begin->format('t') );

        for($i = $begin; $i <= $end; $i->modify('+1 day')){
            if($i->format("D")=="Sun"||$i->format('D')=='Sat'){
                $workingDayCount--;
            } else {
                $specialDay = $specialDays->where('date',$i->format("d"))->first();
                if($specialDay)
                    $workingDayCount--;
            }
        }

        $itineraryDatesCount = $itinerary->itineraryDates->count();

        if($workingDayCount>$itineraryDatesCount){
            throw new WebAPIException("Incomplete itinerary. Please check again.");
        }

        if(isset($itinerary->fieldManager)&&$user->getRoll()==config('shl.field_manager_type')){
            throw new WebAPIException("Forbidden! You haven't permission to approve an itinerary.");
        }

        // if($itinerary->i_aprvd_at) throw new WebAPIException("Itinerary has already confirmed");

        $itinerary->i_aprvd_at = date("Y-m-d H:i:s");

        $itinerary->i_aprvd_u_id = $user->getKey();

        $itinerary->save();

        UserItinerarySubTown::where('i_id',$itinerary->getKey())->update(['uist_approved'=>1]);


        // Sending Email


        $query = Itinerary::where([
            'i_id' => $request->input('id'),
        ])
            ->latest()
            ->with([
            'itineraryDates',
            'itineraryDates.joinFieldWorker',
            'itineraryDates.standardItineraryDate',
            'itineraryDates.standardItineraryDate.bataType',
            'itineraryDates.standardItineraryDate.bataType.bataCategory',
            'itineraryDates.additionalRoutePlan',
            'itineraryDates.additionalRoutePlan.bataType',
            'itineraryDates.additionalRoutePlan.bataType.bataCategory',
            'itineraryDates.changedItineraryDate',
            'itineraryDates.changedItineraryDate.bataType',
            'itineraryDates.changedItineraryDate.bataType.bataCategory',
            'itineraryDates.bataType',
            'itineraryDates.bataType.bataCategory',
            ]);

        $itinerary = $query->first();

        $year = $itinerary->i_year;
        $month = $itinerary->i_month;

        // Aborting if itinerary not found
        if (!$itinerary) {
            abort(404);
        }

        $specialDays = SpecialDay::whereYear('sd_date',$year)->whereMonth('sd_date',$month)->get();

        $specialDays->transform(function($specialDay){
            return [
                'date'=>date('Y-m-d',strtotime($specialDay->sd_date)),
                'description'=>$specialDay->sd_name,
                "types"=>[],
                'towns'=>[]
            ];
        });

        // Formating the itinerary dates
        $itinerary->itineraryDates->transform(function ( ItineraryDate $itineraryDate)use($year,$month,$specialDays) {

            $formatedDate =  $itineraryDate->getFormatedDetails();

            $towns = $formatedDate->getSubTowns();

            $bataType = $formatedDate->getBataType();

            if($bataType){
                $bataType = $bataType->bt_name;
            }

            $date = $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,'0',STR_PAD_LEFT);

                // If join field worker selected areas picking by his itinerary
                if(isset($itineraryDate->joinFieldWorker)){
                $jfwItineraryDate = ItineraryDate::getTodayForUser($itineraryDate->joinFieldWorker,[
                    'bataType',
                    'joinFieldWorker',
                    'additionalRoutePlan',
                    'additionalRoutePlan.bataType',
                    'additionalRoutePlan.areas',
                    'additionalRoutePlan.areas.subTown',
                    'itineraryDayTypes',
                    'itineraryDayTypes.dayType',
                    'standardItineraryDate',
                    'changedItineraryDate',
                ],strtotime($date));
                $jfwFormatedDate = $jfwItineraryDate->getFormatedDetails();
                $towns = $jfwFormatedDate->getSubTowns();
            }

            $type = $formatedDate->getDateType();
            $dayTypes = $formatedDate->getDayTypes();

            $dayTypes = array_map(function($dayType){
                return [
                    'value'=>$dayType->getKey(),
                    'label'=>$dayType->dt_code,
                    'color'=>config('shl.color_codes')[$dayType->dt_color]
                ];
            },$dayTypes);

            $description = "Not Set";
            switch ($type) {
                case 7:
                    $description = "Changed Itinerary";
                    break;
                case 5:
                    $description = "Joint Field Worker - ".$itineraryDate->joinFieldWorker->name;
                    break;
                case 4:
                    $description = "Additional Route Plan";
                    break;
                case 3:
                    $description = "Standard Itinerary";
                    break;
                case 0:
                    $description = "Not a field work day";
                    break;
                case 2:
                    $description = "Not set";
                    break;
            }

            $towns->transform(function(SubTown $subTown){
                return $subTown->sub_twn_name;
            });

            return [
                'date' =>$date ,
                'description' => $description,
                'dayTypes' => $dayTypes,
                'bataType'=>$bataType,
                'mileage'=>$formatedDate->getMileage(),
                'towns'=>$towns->toArray()
            ];
        });

        $itineraryDates = $itinerary->itineraryDates;

        foreach($specialDays as $specialDay){
            $itineraryDate = $itineraryDates->where('date',$specialDay['date'])->first();

            if(!$itineraryDate){
                $itineraryDates->push($specialDay);
            }
        }

        $itineraryDates = $itineraryDates->sortBy('date')->values();

        if($itinerary->rep_id){
            $teamUser = TeamUser::where('u_id', $itinerary->rep_id)->with('team')->latest()->first();

            $parent = User::find($teamUser->team->fm_id);
        } else {
            $team = Team::where('fm_id',$itinerary->fm_id)->latest()->first();

            $parent = User::find($team->hod_id);
        }

        try {
            $emails = EmailSelectedType::where('et_id',1)->with('email')->get();

            $loggedUser = $user;
            $user = User::find($itinerary->rep_id?$itinerary->rep_id: $itinerary->fm_id);

            Mail::send('emails.itinerary-approved', [
                'dates'=> $itineraryDates,
                'user'=> $user,
                'loggedUser'=> $loggedUser,
                'month'=> $yearMonth
            ],function(Message $mail) use($emails,$parent, $user) {

                $mail->subject("[Itinerary Approved] ".$user->u_code);

                $mail->to(config('shl.system_email'),"Onefore CRM");

                if($parent&&!Validator::make(['email'=>$parent->email],['email'=>'email'])->fails()){
                    $mail->cc($parent->email,$parent->name);
                }

                if($user&&!Validator::make(['email'=>$user->email],['email'=>'email'])->fails()){
                    $mail->cc($user->email,$user->name);
                }

                foreach ($emails as $email){
                    if($email->email){
                        $mail->cc($email->email->e_email, $email->email->e_name);
                    }
                }
            });

        } catch (\Exception $e){
            // throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully approved the itinerary!"
        ]);

    }
}
