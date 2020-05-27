<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\DsrProduct;
use App\Models\Product;
use App\Models\User;

class SrProductAllocation extends Base{
    protected $title = "SR Wise Product Allocation";

     protected $columns = [
          'u_id'=>"SR code",
          'product_id'=>"Product Code"
     ];

     protected function formatValue($columnName, $value){
        switch ($columnName) {
            case 'u_id':
                if(!$value)
                    throw new WebAPIException("Please provide a user code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("User not found! Given user code is '$value'");
                return $user->getKey();
            case 'product_id':
                if($value){
                    $product = Product::where((new Product)->getCodeName(),"LIKE",$value)->first();
                    if(!$product)
                        throw new WebAPIException("Product not found! Given code is '$value'");
                    return $product->getKey();
                } else {
                    return null;
                }
            default:
                return ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row){
          DsrProduct::create([
               'dsr_id' => $row['u_id'],
               'product_id' => $row['product_id']
          ]);
    }
}