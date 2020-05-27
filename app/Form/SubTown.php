<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Models\Town;

class SubTown extends Territory {

    protected $title='Sub Town';

    protected $dropdownDesplayPattern = 'sub_twn_name - town.twn_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('town');

    }

    public function beforeSearch($query,$values){
        $query->with('town','town.area');

        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('sub_twn_id')->all();
            $query->whereIn('sub_twn_id',$subTownIds);
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('sub_twn_code')->setLabel('Sub Town Code');
        $inputController->text('sub_twn_name')->setLabel('Sub Town Name');
        $inputController->ajax_dropdown('twn_id')->setLabel('Town')->setLink('town');
    }

    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('sub_twn_id')->all();
            $query->whereIn('sub_twn_id',$subTownIds);
            unset($where['u_id']);
        }

        if(isset($where['ar_id'])){
            $towns = Town::where('ar_id',$where['ar_id'])->get()->pluck('twn_id')->all();

            $query->whereIn('twn_id',$towns);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('sub_twn_id')->all();
            $query->whereIn('sub_twn_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            if($name!='ar_id')
            $query->where($name, $value);
        }
    }
}
