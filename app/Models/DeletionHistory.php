<?php
namespace App\Models;

/**
 * Deletion History
 *
 * Based on issue #162
 *
 * @property string $dh_table_name
 * @property string $dh_primary_key
 * @property string $dh_from_date
 * @property string $dh_to_date
 */
class DeletionHistory extends Base {
    protected $table = 'deletion_history';

    protected $primaryKey = 'dh_id';

    protected $fillable = [
        'dh_table_name',
        'dh_primary_key',
        'dh_from_date',
        'dh_to_date',
    ];
}
