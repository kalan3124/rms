<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\SfaCustomerTarget;
use App\Models\User;

class CustomerTarget extends Base
{
    protected $title = "Customer Monthly Targets";

    protected $lastUser = 0;

    protected $columns = [
        'sfa_cus_code' => "Customer Code",
        'sfa_sr_code' => "Sr Code",
        'sfa_year' => "Year",
        'sfa_month' => "Month (Number)",
        'sfa_target' => 'Target',
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'sfa_sr_code':
                if (!$value) {
                    throw new WebAPIException("Please provide a user code!");
                }

                $user = User::where((new User)->getCodeName(), "LIKE", $value)->first();
                if (!$user) {
                    throw new WebAPIException("User not found! Given user code is '$value'");
                }

                return $user->getKey();
            case 'sfa_cus_code':
                if (!$value) {
                    throw new WebAPIException("Please provide a Customer code!");
                }

                $chemist = Chemist::where((new Chemist)->getCodeName(), "LIKE", $value)->first();
                if (!$chemist) {
                    throw new WebAPIException("Area not found! Given chemist code is '$value'");
                }

                return $chemist->getKey();
            default:
                return ($value <= 0 || !$value) ? null : $value;
        }
    }

    protected function insertRow($row)
    {
        if (is_numeric($row['sfa_year']) && is_numeric($row['sfa_month'])) {
            $targets = SfaCustomerTarget::where('sfa_cus_code', $row['sfa_cus_code'])
                ->where('sfa_sr_code', $row['sfa_sr_code'])
                ->where('sfa_year', $row['sfa_year'])
                ->where('sfa_month', $row['sfa_month'])
                ->first();

            if (isset($targets)) {
                $targets->deleted_at = date('Y-m-d H:i:s');
                $targets->save();
            }

            SfaCustomerTarget::create([
                'sfa_cus_code' => $row['sfa_cus_code'],
                'sfa_sr_code' => $row['sfa_sr_code'],
                'sfa_year' => $row['sfa_year'],
                'sfa_month' => $row['sfa_month'],
                'sfa_target' => $row['sfa_target'],
            ]);
        }
    }
}
