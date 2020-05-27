<?php 
namespace App\Models;

class SfaTarget extends Base{

    protected $table = 'sfa_target';

    protected $primaryKey = 'sfa_trg_id';

    protected $fillable = [
        'u_id',
        'ar_id',
        'trg_year',
        'trg_month'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    public function area(){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }

    public function productTarget(){
        return $this->hasMany(SfaTargetProduct::class,'sfa_trg_id','sfa_trg_id');
    }
}