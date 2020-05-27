<?php 
namespace App\Models;

class SfaUnproductiveVisit extends Base{

    protected $table = 'sfa_unproductive_visits';

    protected $primaryKey = 'sfa_un_id';

    protected $fillable = [
        'un_visit_no',
        'u_id',
        'chemist_id',
        'is_sheduled',
        'rsn_id',
        'latitude',
        'longitude',
        'unpro_time',
        'battery_level',
        'app_version'
    ];

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }
    public function reason(){
        return $this->belongsTo(Reason::class,'rsn_id','rsn_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}