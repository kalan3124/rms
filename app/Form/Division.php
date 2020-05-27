<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;

class Division extends Form{

    protected $title='Division';

    protected $dropdownDesplayPattern = 'divi_name';

    public function beforeDropdownSearch($query,$keyword){
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $query->where('divi_id',$user->divi_id);
        }

        if ($user->getRoll() == config('shl.head_of_department_type')) {
            $query->where('divi_id',$user->divi_id);
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('divi_name')->setLabel('Division Name');
        $inputController->text('divi_short_name')->setLabel('Division Short Name');
    }

}