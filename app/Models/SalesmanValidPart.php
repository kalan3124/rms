<?php 
namespace App\Models;

use App\Exceptions\MediAPIException;

class SalesmanValidPart extends Base{

    protected $table = 'salesman_valid_parts';

    protected $primaryKey = 'smv_part_id';

    protected $fillable = [
        'smv_part_id',
        'salesman_code',
        'contract',
        'catalog_no',
        'customer_id',
        'from_date',
        'to_date',
        'last_updated_on',
        'u_id',
        'product_id'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public static function userAllocatedProduct($user,$with=[],$today=null){
        if(!$today) $today= time();

        $productQuery = self::with($with)
            ->where('u_id',$user->getKey())
            ->whereDate('from_date','<=',date('Y-m-d',$today))
            ->whereDate('to_date','>=',date('Y-m-d',$today))
            ->get();

            return $productQuery;
    }
}