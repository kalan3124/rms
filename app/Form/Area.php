<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class Area extends Territory{

    protected $title='Area';


    protected $dropdownDesplayPattern = 'ar_name - region.rg_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('region');
    }

    public function beforeSearch($query,$values){
        $query->with('region','region.district');

        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('ar_id')->all();
            $query->whereIn('ar_id',$subTownIds);
        }
    }


    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('ar_name')->setLabel('Area Name');
        $inputController->text('ar_short_name')->setLabel('Short Name');
        $inputController->text('ar_code')->setLabel('Area Code')->isUpperCase();
        $inputController->ajax_dropdown('rg_id')->setLabel('Region')->setLink('region');
        $inputController->setStructure([['ar_name'],['ar_short_name','ar_code'],'rg_id']);
    }

    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('ar_id')->all();
            $query->whereIn('ar_id',$subTownIds);
            unset($where['u_id']);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('ar_id')->all();
            $query->whereIn('ar_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            $query->where($name, $value);
        }
    }

}