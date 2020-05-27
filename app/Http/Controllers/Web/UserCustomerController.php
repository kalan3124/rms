<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\UserCustomer;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;

class UserCustomerController extends Controller
{
    public function loadByUser(Request $request){
        $validation = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|numeric|exists:users,id'
        ]);

        if($validation->fails()) {
            throw new WebAPIException("Please provide a user to load customers.");
        }

        $user = $request->input('user');

        $user = User::find($user['value']);

        $userCustomers = UserCustomer::getByUser($user);

        $chemists = [];
        $doctors = [];
        $otherHospitalStaffs = [];

        foreach($userCustomers as $customer){
            if(isset($customer->chemist)){
                $chemists[] = [
                    'value'=>$customer->chemist->getKey(),
                    'label'=>$customer->chemist->chemist_name
                ];
            } else if(isset($customer->doctor)){
                $doctors[] = [
                    'value'=>$customer->doctor->getKey(),
                    'label'=>$customer->doctor->doc_name
                ];
            } else if (isset($customer->otherHospitalStaff)){
                $otherHospitalStaffs[] = [
                    'value'=>$customer->otherHospitalStaff->getKey(),
                    'label'=>$customer->otherHospitalStaff->hos_stf_name
                ];
            }
        }

        return response()->json([
            'chemists'=>$chemists,
            'doctors'=>$doctors,
            'staffs'=>$otherHospitalStaffs
        ]);
    }

    public function create(Request $request){

        $validation = Validator::make($request->all(),[
            "user"=>'required|array',
            'user.value'=>'required|numeric|exists:users,id',
            'customer'=>'required|array',
            'customer.value'=>'required|numeric',
            'type'=>'required|in:chemist,doctor,other_hospital_staff'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $user = $request->input('user');
        $type = $request->input('type');
        $customer = $request->input('customer');

        $data = [
            'u_id'=>$user['value']
        ];

        if($type=='chemist'){
            $data['chemist_id'] = $customer['value'];
        } else if ($type=="other_hospital_staff") {
            $data['hos_stf_id'] = $customer['value'];
        } else {
            $data['doc_id'] = $customer['value'];
        }

        UserCustomer::firstOrCreate($data);

        return response()->json([
            'success'=>true,
            "message"=>"you have successfully assigned the customer to user."
        ]);

    }

    public function remove(Request $request){

        $validation = Validator::make($request->all(),[
            "user"=>'required|array',
            'user.value'=>'required|numeric|exists:users,id',
            'customer'=>'required|array',
            'customer.value'=>'required|numeric',
            'type'=>'required|in:chemist,doctor,other_hospital_staff'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $user = $request->input('user');
        $type = $request->input('type');
        $customer = $request->input('customer');

        $data = [
            'u_id'=>$user['value']
        ];

        if($type=='chemist'){
            $data['chemist_id'] = $customer['value'];
        }  else if ($type=="other_hospital_staff") {
            $data['hos_stf_id'] = $customer['value'];
        } else {
            $data['doc_id'] = $customer['value'];
        }

        UserCustomer::where($data)->delete();

        return response()->json([
            'success'=>true,
            "message"=>"you have successfully deleted the assigned customer."
        ]);

    }

    public function removeAll(Request $request){
        $userId = $request->input('user.value');

        if(!$userId)
            throw new WebAPIException("Invalid request sent. Please try again after refreshing your browser");

        UserCustomer::where('u_id',$userId)->delete();

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully deleted all customer allocations from the user"
        ]);
    }
}
