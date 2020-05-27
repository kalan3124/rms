<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\GPSTracking;
use App\Models\Team;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class HodGpsTrackingController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|array',
            'user.value' => 'required|numeric|exists:users,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new WebAPIException($validator->errors()->first());
        }

        $date = $request->input('date');
        $user = $request->input('user');
        $userId = $user['value'];

        $team = Team::where('hod_id', $userId)->get();
        $teams_users = TeamUser::whereIn('tm_id', $team->pluck('tm_id')->all())->get();
        $hod_users = $teams_users->pluck('u_id');

        if (count($hod_users) <= 0) {
            throw new WebAPIException('Can not find any Team users for Hod');
        }

        $coordinates = [];
        foreach ($hod_users as $key => $val) {
            $user = User::find($val);
            $coordinate = GPSTracking::where('u_id', $val)->latest()->first();
            $coordinates[$val] = [
                'lat' => $coordinate['gt_lat'],
                'lng' => $coordinate['gt_lon'],
                'btry' => $coordinate['gt_btry']?$coordinate['gt_btry']:0,
                'code' => isset($user->u_code) ? $user->u_code : '',
                'name' => isset($user->name) ? $user->name : '',
                'time' => $coordinate['gt_time']?$coordinate['gt_time']:''
            ];
        }

        return response()->json([
            'coordinates' => $coordinates,
            'hodUsers' => $hod_users,
        ]);
    }
}
