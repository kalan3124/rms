<?php 
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class Region Extends Territory 
{
    protected $title='Region';

    protected $dropdownDesplayPattern = 'rg_name - district.dis_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('district');
    }

    public function beforeSearch($query,$values){
        $query->with('district','district.province');

        $user= Auth::user();

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('rg_id')->all();
            $query->whereIn('rg_id',$subTownIds);
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('rg_code')->setLabel('Region Code');
        $inputController->text('rg_name')->setLabel('Region Name');
        $inputController->ajax_dropdown('dis_id')->setLabel('District')->setLink('district');
    }


    public function filterDropdownSearch($query, $where)
    {
        $user= Auth::user();

        if(isset($where['u_id'])){
            $areas = $this->getUserAreas($where['u_id']);
            $subTownIds = $areas->pluck('rg_id')->all();
            $query->whereIn('rg_id',$subTownIds);
            unset($where['u_id']);
        }

        if($user->u_tp_id!=1){
            $areas = $this->getUserAreas($user->getKey());
            $subTownIds = $areas->pluck('rg_id')->all();
            $query->whereIn('rg_id',$subTownIds);
        }

        foreach ($where as $name => $value) {
            $query->where($name, $value);
        }
    }
}