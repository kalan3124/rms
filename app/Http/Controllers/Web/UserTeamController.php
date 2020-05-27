<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\UserTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserTeamController extends Controller {
    public function load(Request $request){
        $userId = $request->input('user');

        $teamUsers = UserTeam::with('team')->where('u_id',$userId)->get();

        return $teamUsers->map(function(UserTeam $userTeam){
            return [
                'value'=>$userTeam->team?$userTeam->team->tm_id:0,
                'label'=>$userTeam->team?$userTeam->team->tm_name:"DELETED"
            ];
        });

    }

    public function save(Request $request){
        $teams = $request->input('teams');
        $users = $request->input('users');

        try{
            DB::beginTransaction();

            foreach ($users as $key => $user) {
                UserTeam::where('u_id',$user['value'])->delete();
                foreach ($teams as $key => $team) {

                    UserTeam::create([
                        'tm_id'=>$team['value'],
                        'u_id'=>$user['value']
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw new WebAPIException("Server error appeared. Please contact system vendor.");
        }

        return response()->json([
            'success'=>true,
            "message"=>"Successfully allocated the teams for the user"
        ]);
    }
}