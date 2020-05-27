<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class Town extends Territory{

    protected $title='Town';

    protected $dropdownDesplayPattern = 'twn_name - area.ar_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('area');
    }

    public function beforeSearch($query,$values){
        $query->with('area','area.region');

        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('twn_id')->all();
            $query->whereIn('twn_id',$subTownIds);
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('twn_name')->setLabel('Town Name');
        $inputController->text('twn_short_name')->setLabel('Short Name');
        $inputController->text('twn_code')->setLabel('Town Code')->isUpperCase();
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area');
        $inputController->setStructure([['twn_name'],['twn_short_name','twn_code'],'ar_id']);
    }


    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('twn_id')->all();
            $query->whereIn('twn_id',$subTownIds);
            unset($where['u_id']);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('twn_id')->all();
            $query->whereIn('twn_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            $query->where($name, $value);
        }
    }

}