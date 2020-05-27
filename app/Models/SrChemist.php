<?php
namespace App\Models;

class SrChemist extends Base{

     protected $table='sr_chemist_details';

     protected $primaryKey = 'sr_chem_id';

     protected $fillable = [
         'chem_name','owner_name','address','mobile number','email','lat','lon','image_url','update_status','added_by','created_u_id'
     ];

     public function user(){
         $this->belongsTo(User::class,'created_u_id','id');
     }

}

?>