<?php
namespace App\Models;

class Issue extends Base{
    protected $table="issues";

    protected $primaryKey = "i_id";

    protected $fillable = ["u_id","i_due_date","i_cmplt_date","i_application","i_module","i_description","i_title","i_cmnt_shl","i_cmnt_cl","i_status",'i_label','i_num'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}