<?php

namespace App\Models;

use App\Models\Base;
use Illuminate\Database\Eloquent\Collection;

/**
 * Company Return Model
 *
 * @property int $cr_id AI Key
 * @property int $grn_id
 * @property int $u_id
 * @property string $cr_remark
 * @property float $cr_amount
 * @property string $cr_number
 * @property string $cr_confirmed_at Confirmed time
 * @property int $dis_id
 *
 * @property GoodReceivedNote $goodReceivedNote
 * @property User $createdUser
 * @property User $distributor
 * @property Collection|CompanyReturnLine[] $lines
 */
class CompanyReturn extends Base {
    protected $table = 'company_return';

    protected $primaryKey = 'cr_id';

    protected $codeName = 'cr_number';

    protected $fillable = [
        'grn_id',
        'u_id',
        'dis_id',
        'cr_remark',
        'cr_amount',
        'cr_number',
        'cr_confirmed_at'
    ];

    public function goodReceivedNote(){
        return $this->belongsTo(GoodReceivedNote::class,'grn_id','grn_id');
    }

    public function createdUser(){
        return $this->belongsTo(User::class, 'u_id','id');
    }

    public function lines(){
        return $this->hasMany(CompanyReturnLine::class,'cr_id','cr_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public static function generateNumber($disId){
        $count = self::where('dis_id', $disId)->count();

        return "CR/". str_pad($disId,3,"0",STR_PAD_LEFT)."/".str_pad(($count+1),3,"0",STR_PAD_LEFT);
    }
}
