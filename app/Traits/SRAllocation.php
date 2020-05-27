<?php 
namespace App\Traits;

use App\Models\SalesmanValidPart;

/**
 * Returning the allocated products by for sales rep
 */
trait SRAllocation
{

    public function getProductsBySR($user,$today){

        $salesmanPro = SalesmanValidPart::userAllocatedProduct($user,['user','product'],$today);

        $salesmanPro->transform(function($smp){
            return [
                'sr_id'=>$smp->u_id,
                'sr_code'=>$smp->salesman_code,
                'sr_name'=>$smp->user?$smp->user->name:"",
                'contract'=>$smp->contract,
                'product_id'=>$smp->product_id,
                'catalog_no'=>$smp->catalog_no,
                'product_name'=>$smp->product?$smp->product->product_name:"",
                'from_date'=>$smp->from_date,
                'to_date'=>$smp->to_date,
            ];
        });

        return $salesmanPro;
    }
    
}
