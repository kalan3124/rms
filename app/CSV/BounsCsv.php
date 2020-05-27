<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusFreeProduct;
use App\Models\BonusProduct;
use App\Models\BonusRatio;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BounsCsv extends Base{
    protected $title = "Bouns";

    protected $lastCode = 0;

     protected $columns = [
          'bns_code'=>'Code',
          'bns_name'=>"Description",
          'bns_start_date'=>"Start Date (YYYY-MM-DD)",
          'bns_end_date'=>"End Date (YYYY-MM-DD)",
          'normal_part'=>'Normal Part',
          'free_part'=>'Free Part',
          'dis'=>'Distributor',
          'min'=>'Min',
          'max'=>'Max',
          'purchase'=>'Purchase',
          'free'=>'Free'
     ];

     protected function insertRow($row){
          $str = $row['bns_code'];
          $bns_code = explode(".",$str);

         if($this->lastCode != $bns_code[0]){
              $bouns = Bonus::create([
                    'bns_name' => $row['bns_name'],
                    'bns_code' => $bns_code[0],
                    'bns_start_date' => date('Y-m-d',strtotime($row['bns_start_date'])),
                    'bns_end_date' => date('Y-m-d',strtotime($row['bns_end_date'])),
                    'bns_all' => strtolower($row['dis']) == "all"||empty(trim($row['dis']))?1:0
              ]);

              $product_normal = explode(',',$row['normal_part']);
              $product_free = explode(',',$row['free_part']);

               foreach ($product_normal as $key => $val) {
                    $pro = Product::where('product_code',$val)->first();

                    if(!isset($pro)){
                         throw new WebAPIException("Normal Product Not Found ".$pro->product_code);
                    }

                    BonusProduct::create([
                         'bns_id'=>$bouns->getKey(),
                         'product_id'=>$pro->getKey()
                    ]);
               }

               foreach ($product_free as $key => $val) {
                    $pro = Product::where('product_code',$val)->first();

                    if(!isset($pro)){
                         throw new WebAPIException("Free Product Not Found ".$pro->product_code);
                    }
                    
                    BonusFreeProduct::create([
                         'bns_id'=>$bouns->getKey(),
                         'product_id'=>$pro->getKey()
                    ]);
               }   

               if(strtolower($row['dis']) != "all"&&!empty(trim($row['dis']))){
                    $dis = explode(',',$row['dis']);

                    foreach ($dis as $key => $val) {
                         $user = User::where('u_code',$val)->first();
                         
                         if(isset($user)){
                              BonusDistributor::create([
                                   'dis_id' => $user->getKey(),
                                   'bns_id' => $bouns->getKey()
                              ]);
                         } else {
                              throw new WebAPIException("User Not Found ".$user->u_code);
                         }
                         
                    }
               }
               
               
         } else {
              $bouns = Bonus::where('bns_code',$this->lastCode)->first();
         }
         
         BonusRatio::create([
               'bns_id' => $bouns->getKey(),
               'bnsr_min' => $row['min'],
               'bnsr_max' => $row['max'],
               'bnsr_purchase' => $row['purchase'],
               'bnsr_free' => $row['free']
         ]);
         
         $this->lastCode = $bns_code[0];
     }
}
?>