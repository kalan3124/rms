<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Tax Codes
 * @property int $tax_code_id Auto Increment
 * @property string $company
 * @property string $fee_code
 * @property string $description
 * @property double $fee_rate
 * @property string $valid_from
 * @property string $valid_until
 * @property string $fee_type
 *
 */

class TaxCode extends Base {

    protected $table = 'tax_codes';

    protected $primaryKey = 'tax_code_id';

    protected $codeName = 'fee_code';

    protected $fillable = [
        'company','fee_code','description', 'fee_rate', 'valid_from', 'valid_until', 'fee_type'
    ];

}
