<?php

namespace App\Models;

/**
 * Last integration times and status
 * 
 * @property string $mysql_table_name ext table name
 * @property string $last_sync_time Last synced time
 * @property int $last_sync_status 1 = Finished | 0 = Not Finished/ Stopped due to an error
 */
class IntegrationSyncTime extends Base
{
    protected $table = 'integration_sync_time';

    protected $primaryKey = 'int_sync_id';

    protected $fillable = [
        'mysql_table_name','last_sync_time','last_sync_status'
    ];
}
