<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\SfaUserLogin;
use Illuminate\Http\Request;
use App\Exceptions\WebAPIException;

class UserLoginDetailsReportController extends ReportController{
     protected $title = "SR Login Details Report";

    public function search(Request $request){
          $values = $request->input('values');

          if(!isset($values['user'])){
               throw new WebAPIException('User field is required');
          }

         $query = SfaUserLogin::query();
         $query->with('user');
         $query->where('u_id',$values['user']['value']);

         if(isset($values['date'])){
              $query->whereDate('login_date','=',date('Y-m-d',strtotime($values['date'])));
         }
         $results = $query->get();
        
         $results->transform(function($val){
              return[
                   'user_code'=>$val->user->u_code,
                   'user_name'=>$val->user->name,
                   'login_date'=>$val->login_date
              ];
         });

         return[
              'results' => $results,
              'count' => 0
         ];
    }

     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text("user_code")->setLabel("User Code");
          $columnController->text("user_name")->setLabel("User Name");
          $columnController->text("login_date")->setLabel("Login Date");
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown("user")->setWhere(['u_tp_id'=>'10'])->setLabel("MR/PS or FM")->setLink("user");
          $inputController->date("date")->setLabel("Date");
          
          $inputController->setStructure([["user","date"]]);
     }
}
?>