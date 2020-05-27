<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class District extends Territory{

    protected $title='Distirct';

    protected $dropdownDesplayPattern = 'dis_name - province.pv_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('province');
    }

    public function beforeSearch($query,$values){
        $query->with('province');

        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('dis_id')->all();
            $query->whereIn('dis_id',$subTownIds);
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('dis_name')->setLabel('District Name');
        $inputController->text('dis_short_name')->setLabel('District Short Name');
        $inputController->text('dis_code')->setLabel('District Code')->isUpperCase();
        $inputController->ajax_dropdown('pv_id')->setLabel('Province')->setLink('province')->setLimit(false);
        $inputController->setStructure([['dis_name'],['dis_short_name','dis_code'],'pv_id']);
    }

    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('dis_id')->all();
            $query->whereIn('dis_id',$subTownIds);
            unset($where['u_id']);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('dis_id')->all();
            $query->whereIn('dis_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            $query->where($name, $value);
        }
    }
}