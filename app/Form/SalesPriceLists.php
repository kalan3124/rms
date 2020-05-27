<?php 
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Form\Columns\ColumnController;

class SalesPriceLists extends Form {
     protected $title='Sale Price List';

     protected $dropdownDesplayPattern = 'description - price_list_no';

     public function setInputs(\App\Form\Inputs\InputController $inputController)
     {
          $inputController->text('price_list_no')->setLabel('Price List No');
          $inputController->text('description')->setLabel('description');
          $inputController->date('valid_from_date')->setLabel('Valid Date');
     }

     public function filterDropdownSearch($query, $where)
     {
          $query->groupBy('price_list_no');
     }
}