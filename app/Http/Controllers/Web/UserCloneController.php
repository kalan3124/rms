<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeamUser;
use App\Models\TeamUserProduct;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\StandardItinerary;
use App\Models\StandardItineraryDate;
use App\Models\StandardItineraryDateArea;
use App\Models\UserArea;
use App\Models\UserCustomer;
use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserCloneController extends Controller {
    public function getItems(){
        return [
            'sections'=>[
                '1'=>'Team Allocations',
                '2'=>"Current Standard Itinerary",
                '3'=>"Area Allocations",
                "4"=>"Chemist Allocations",
                "5"=>"Doctor Allocations"
            ]
        ];
    }

    public function cloneUser(Request $request){
        $validation = Validator::make($request->all(),[
            'sectionIds'=>'required|array',
            'id'=>'required|exists:users,id',
            'values'=>'required|array',
            'values.name'=>'required',
            'values.empCode'=>'required',
            'values.userName'=>'required',
            'values.password'=>'required',
            'values.email'=>'required',
            'values.contact'=>'required'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request!");
        }

        try {
            DB::beginTransaction();

            $user = User::where('id',$request->input('id'))->first();
            $newUser = $user->replicate();
            $newUser->user_name = $request->input('values.userName');
            $newUser->password = Hash::make($request->input('values.password'));
            $newUser->name = $request->input('values.name');
            $newUser->u_code = $request->input('values.empCode');
            $newUser->email = $request->input('values.email');
            $newUser->contact_no = $request->input('values.contact');
            $newUser->push();

            $sectionIds = array_flip($request->input('sectionIds')) ;

            // Cloning team allocation
            if(isset($sectionIds[1])){
                $teamMember = TeamUser::where('u_id',$user->getKey())->latest()->first();
                if($teamMember){
                    $newTeamMember = $teamMember->replicate();
                    $newTeamMember->u_id = $newUser->getKey();
                    $newTeamMember->push();

                    $teamUserProducts = TeamUserProduct::where('tmu_id',$teamMember->getKey())->get();

                    foreach ($teamUserProducts as $key => $teamUserProduct) {
                        $newTeamUserProduct = $teamUserProduct->replicate();
                        $newTeamUserProduct->tmu_id = $newTeamMember->getKey();
                        $newTeamUserProduct->push();
                    }
                }
            }
            /** @var \App\Models\User $user */
    
            if(isset($sectionIds[2])&&in_array($user->getRoll(),[
                config('shl.product_specialist_type'),
                config('shl.medical_rep_type'),
                config('shl.field_manager_type')
            ])){

                // Cloning standard itinerary
                $oldStandardItinerary = StandardItinerary::where('u_id',$user->getKey())->latest()->first();
                $newStandardItinerary = $oldStandardItinerary->replicate();
                $newStandardItinerary->u_id = $newUser->getKey();
                $newStandardItinerary->save();

                if($oldStandardItinerary){
                    $oldStandardItineraryId = $oldStandardItinerary->getKey();

                    $oldDates = StandardItineraryDate::where('si_id',$oldStandardItineraryId)->get();

                    foreach ($oldDates as $oldDate) {
                        $newDate = $oldDate->replicate();
                        $newDate->si_id = $newStandardItinerary->getKey();
                        $newDate->save();

                        $oldAreas = StandardItineraryDateArea::where('sid_id',$oldDate->getKey())->get();

                        foreach ($oldAreas as $key => $oldArea) {
                            $newArea = $oldArea->replicate();
                            $newArea->sid_id = $newDate->sid_id;
                            $newArea->save();
                        }
                        
                    }
                }
            }

            // Cloning area allocations
            if(isset($sectionIds[3])){
                $areas = UserArea::where('u_id',$user->getKey())->get();

                foreach ($areas as $key => $area) {
                    $newArea = $area->replicate();
                    $newArea->u_id = $newUser->getKey();
                    $newArea->push();
                }
            }

            // Chemist allocations
            if(isset($sectionIds[4])){
                $doctors = UserCustomer::where('u_id',$user->getKey())->whereNull('doc_id')->get();

                foreach($doctors as $doctor ){
                    $newDoctor = $doctor->replicate();

                    $newDoctor->u_id = $newUser->getKey();

                    $newDoctor->push();
                }
            }
        
            // Doctor allocations
            if(isset($sectionIds[5])){
                $doctors = UserCustomer::where('u_id',$user->getKey())->whereNull('doc_id')->get();

                foreach($doctors as $doctor ){
                    $newDoctor = $doctor->replicate();

                    $newDoctor->u_id = $newUser->getKey();

                    $newDoctor->push();
                }
            } 

            DB::commit();
        } catch (\Exception $e){
            DB::rollback();
            throw $e;
            throw new WebAPIException("Server error apeared!");
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully cloned the user!"
        ]);
    }
}