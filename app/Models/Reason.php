<?php

namespace App\Models;

/**
 * Reasons for all things
 *
 * @property string $rsn_name
 * @property int $rsn_type
 *
 * @property ReasonType $reason_type
 */
class Reason extends Base
{
    protected $table = 'reason';

    protected $primaryKey = 'rsn_id';

    protected $fillable = [
        "rsn_name","rsn_type"
    ];

    protected $codeName = 'rsn_name';

    public function reason_type (){
        return $this->belongsTo(ReasonType::class,'rsn_type','rsn_tp_id');
    }
}
