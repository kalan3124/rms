<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SrChemist;
use App\Models\User;

class SrChemistController extends ReportController{

     protected $title = "Sr Chemist Details";

     public function search(Request $request){
          $sr_id =  $request->input('values.user.value');

          $sr_chemists = SrChemist::query();

          if(isset($sr_id)){
               $sr_chemists->where('added_by',$sr_id);
          }

          $results = $sr_chemists->get();
          
          $results->transform(function($row){
               $user = User::where('id',$row->created_u_id)->latest()->first();
               return[
                    'chemi_name' => $row->chem_name,
                    'owner_name' => $row->owner_name,
                    'address' => $row->address,
                    'mobile' => $row->mobile_number,
                    'email' => $row->email,
                    'lat' => $row->lat,
                    'lon' => $row->lon,
                    'added_by' => $user?$user->name:"",
                    'image_url' => $row->image_url
               ];
          });

          return[
               'results'=>$results,
               'count'=>0
          ];
     }

     protected function setColumns(ColumnController $columnController,Request $request){

          $columnController->text('chemi_name')->setLabel("Chemist Name");
          $columnController->text('owner_name')->setLabel("Owner Name");
          $columnController->text('address')->setLabel("Address");
          $columnController->text('mobile')->setLabel("Mobile");
          $columnController->text('email')->setLabel("Email");
          $columnController->text('lat')->setLabel("Lat");
          $columnController->text('lon')->setLabel("Lon");
          $columnController->text('added_by')->setLabel("Added By");
          $columnController->image('image_url')->setLabel("Image");
     }
     protected function setInputs($inputController){
         $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=> '10']);
         $inputController->setStructure([
             ['user']
         ]);
     }
}
?>