<?php
namespace App\Form;

use App\Models\DistributorSite;

class Site extends Form 
{
    protected $title='Sites';

    protected $dropdownDesplayPattern = 'site_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('site_name')->setLabel('Name');
        $inputController->text('site_code')->setLabel('Code');
    }

    public function filterDropdownSearch($query,$where){
        if(isset($where['dis_id'])){
            $distributorSites = DistributorSite::where('dis_id',$where['dis_id'])->get();

            $query->whereIn('site_id',$distributorSites->pluck('site_id')->all());

            unset($where['dis_id']);
        }

        parent::filterDropdownSearch($query,$where);
    }

}