<?php 
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Traits\Team;

class Brand extends Form{

    use Team;

    protected $title = 'Product Brand';

    protected $dropdownDesplayPattern = 'brand_name';

    public function beforeDropdownSearch($query,$keyword){
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $products = $this->getProductsByUser($user,['brand']);

            $query->whereIn('brand_id',$products->pluck('brand.brand_id')->all());
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
            $products = $this->getProductsByUser($user,['brand']);

            $query->whereIn('brand_id',$products->pluck('brand.brand_id')->all());
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('brand_name')->setLabel('Product Brand Name');
    }
}