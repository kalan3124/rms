<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class Province extends Territory{

    protected $title='Province';

    protected $dropdownDesplayPattern = 'pv_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('pv_name')->setLabel('Province Name');
        $inputController->text('pv_short_name')->setLabel('Short Name');
        $inputController->text('pv_code')->setLabel('Province Code')->isUpperCase();
        $inputController->setStructure([['pv_name'],['pv_short_name','pv_code']]);
    }


    public function beforeSearch($query,$values){
        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('pv_id')->all();
            
            $query->whereIn('pv_id',$subTownIds);
        }
    }

    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('pv_id')->all();
            $query->whereIn('pv_id',$subTownIds);
            unset($where['u_id']);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('pv_id')->all();
            $query->whereIn('pv_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            $query->where($name, $value);
        }
    }
}