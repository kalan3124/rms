<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\AccSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use \Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), ['username' => 'required|exists:users,user_name', 'password' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Can not validate some inputs!",
                "errors" => $validator->errors(),
            ]);
        }

        $users = User::where('user_name', $request->username)->first();

        if ($users->fail_attempt == 3) {
            return response()->json([
                'success' => false,
                'message' => 'Your user account has been temporary blocked.Please contact System Admin',
            ]);
        }

        $date = date_create((string) $users->u_password_created);
        $current = date_create((string) date('Y-m-d'));

        $interval = date_diff($date, $current);

        $attempt = AccSettings::where('st_id',2)->first();

        if ($attempt->duration < [$interval->days][0] && !isset($request->newPassword) && Hash::check($request->password, $users->password)) {
            return response()->json([
                'success' => false,
                'status' => true,
                'message' => 'Your Password has been expired. Please Change the password',
            ]);
        }

        if (Auth::attempt(['user_name' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            $users->fail_attempt = 0;
            $users->save();

            if (isset($request->newPassword)) {
                $users->password = Hash::make($request->newPassword);
                $users->u_password_created = date('Y-m-d');
                $users->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully login! Please wait we are redirecting you to our homepage.',
                'token' => $user->createToken('WebApp')->accessToken,
            ]);
        } else {

            if ($users->u_tp_id != 1) {
                $users->fail_attempt += 1;
                $users->save();
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password! Make sure CAPSLOCK key is turn off',
                'errors' => [
                    'password' => ['Invalid Password Provided!'],
                ],
            ]);
        }
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();

        return [
            'id' => $user->getKey(),
            'name' => $user->getName(),
            'roll' => $user->getRoll(),
            'picture' => $user->getProfilePicture(),
            'time' => date('Y-m-d H:i:s'),
            'code' => $user->u_code,
        ];
    }

    public function updateUserDetails(Request $request)
    {
        $password = $request->input('password');
        $lock_time = $request->input('lock_time');
        $days = $request->input('attempts');
        $user = Auth::user();

        if (!isset($password)) {
            throw new WebAPIException("Password Field is Empty");
        }

        $change = User::where('id', $user->getKey())->first();
        $change->password = Hash::make($password);
        $change->save();

        $lock = AccSettings::where('st_id',1)->first();
        $lock->duration = $lock_time;
        $lock->save();

        $attempt = AccSettings::where('st_id',2)->first();
        $attempt->duration = $days;
        $attempt->save();

        return [
            'message' => "User Data has been Updated",
        ];
    }

    public function loadOtherDetails(){
        $lock = AccSettings::where('st_id',1)->first();
        $attempt = AccSettings::where('st_id',2)->first();

        return[
            'lock_time' => $lock->duration,
            'attempts' => $attempt->duration,
        ];
    }

    public function otherDetails(Request $request){
        $lock_time = $request->input('lock_time');
        $days = $request->input('attempts');

        $lock = AccSettings::where('st_id',1)->first();
        $lock->duration = $lock_time;
        $lock->save();

        $attempt = AccSettings::where('st_id',2)->first();
        $attempt->duration = $days;
        $attempt->save();

        return [
            'message' => "User parameters has been Updated",
        ];
    }
}
