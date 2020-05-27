<?php 
namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use App\Models\UserCustomer;
use App\Models\Chemist;
use \Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Traits\Territory;

class ChemistController extends controller{

    use Territory;

    public function chemists(){

        $user= Auth::user();

        // $allocatedUsers = User::getByUser($user);

        // $userCustomers = UserCustomer::where('u_id',$allocatedUsers->pluck('id')->all())->whereNull('doc_id')->with('chemist')->get();
        $getAllocatedTerritories = $this->getAllocatedTerritories($user);

        $userCustomers = Chemist::whereIn('sub_twn_id',$getAllocatedTerritories->pluck('sub_twn_id')->all())->get();
        //get chemist details

        // return $userCustomers;
        $userCustomers->transform(function ($userCustomer, $key) {
            return [
                'chem_id'=>$userCustomer->chemist_id,
                'chem_code'=>$userCustomer->chemist_code,
                'chem_name'=>$userCustomer->chemist_name,
                'chem_address'=>$userCustomer->chemist_address,
                'town_id'=>$userCustomer->sub_twn_id??0,
                'town_name'=>$userCustomer->sub_town?$userCustomer->sub_town->sub_twn_name:"",
                'chem_class_id'=>$userCustomer->chemist->chemist_class_id??0,
                'chem_class_name'=>$userCustomer->chemist_class?$userCustomer->chemist_class->chemist_class_name:"",
                'chem_type_id'=>$userCustomer->chemist_type_id??0,
                'chem_type'=>$userCustomer->chemist_types?$userCustomer->chemist_types->chemist_type_name:"",
                'chem_market_description_id'=>$userCustomer->chemist_mkd_id??0,
                'chem_market_description'=>$userCustomer->chemist_market_description?$userCustomer->chemist_market_description->chemist_mkd_name:""
            ];
        });
        
        return [
            'result'=>true,
            'chemists'=>$userCustomers,
            'count'=>$userCustomers->count()
        ];
    }
}