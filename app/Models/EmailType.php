<?php
namespace App\Models;

/**
 * Email Type Model
 *
 * @property int $et_id
 * @property string $et_name
 */
class EmailType extends Base {

    protected $table = 'email_types';

    protected $primaryKey = 'et_id';

    protected $fillable = [
        'et_name'
    ];
}
