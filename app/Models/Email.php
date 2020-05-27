<?php
namespace App\Models;

/**
 * Email Model
 *
 * @property int $e_id
 * @property string $e_email
 * @property string $e_name
 */
class Email extends Base {

    protected $table = 'emails';

    protected $primaryKey = 'e_id';

    protected $fillable = [
        'e_email',
        'e_name'
    ];


}
