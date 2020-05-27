<?php
namespace App\Models;

/**
 * Selected Email Types for an email
 *
 * @property int $e_id
 * @property int $et_id
 *
 * @property Email $email
 * @property EmailType $emailType
 */
class EmailSelectedType extends Base {

    protected $table = 'selected_email_types';

    protected $primaryKey = 'set_id';

    protected $fillable = [
        'e_id',
        'et_id'
    ];

    public function email(){
        return $this->belongsTo(Email::class,'e_id','e_id');
    }

    public function emailType(){
        return $this->belongsTo(EmailType::class,'et_id','et_id');
    }
}
