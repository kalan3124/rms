<?php 
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Traits\Team;

class ProductFamily extends Form {
    use Team;

    protected $title = 'Product Families';

    protected $dropdownDesplayPattern = 'product_family_name';

    public function beforeDropdownSearch($query,$keyword){
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $products = $this->getProductsByUser($user,['product_family']);

            $query->whereIn('product_family_id',$products->pluck('product_family.product_family_id')->all());
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
            $products = $this->getProductsByUser($user,['product_family']);

            $query->whereIn('product_family_id',$products->pluck('product_family.product_family_id')->all());
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('product_family_name')->setLabel('Product Family Name');
    }
}