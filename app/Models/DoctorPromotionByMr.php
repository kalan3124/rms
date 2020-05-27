<?php 
namespace App\Models;

 class DoctorPromotionByMr extends Base{

    protected $table = 'doctor_promotion_by_mr';

    protected $primaryKey = 'dpbmr_id';

    protected $fillable = [
        'u_id',
        'doc_id',
        'promo_id',
        'vt_id',
        'head_count',
        'promo_value',
        'promo_date',
        'promo_lon',
        'promo_lat',
        'bat_lvl',
        'image_url',
        'app_version'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }
    public function promotion(){
        return $this->belongsTo(Promotion::class,'promo_id','promo_id');
    }
    public function visit_place(){
        return $this->belongsTo(VisitType::class,'vt_id','vt_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function details(){
        return $this->hasMany(DoctorPromotionDetailsByMr::class,"dpbmr_id","dpbmr_id");
    }
 }