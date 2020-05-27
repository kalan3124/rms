<?php 
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Traits\Team;

class Principal extends Form {
    use Team;

    protected $title = 'Product Principal';

    protected $dropdownDesplayPattern = 'principal_name';

    public function beforeDropdownSearch($query,$keyword){
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $products = $this->getProductsByUser($user,['principal']);

            $query->whereIn('principal_id',$products->pluck('principal.principal_id')->all());
        }
    }

    public function beforeSearch($query,$values){
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $products = $this->getProductsByUser($user,['principal']);

            $query->whereIn('principal_id',$products->pluck('principal.principal_id')->all());
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('principal_code')->setLabel('Product Principal Code')->isUpperCase();
        $inputController->text('principal_name')->setLabel('Product Principal Name');
    }
}