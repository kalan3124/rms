<?php

namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\User;
use App\Models\Product;
use App\Models\Area;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;

class SrTarget extends Base
{
    protected $title = "SR Wise Area Targets";

    protected $lastUser = 0;

    protected $monthNames = ['apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'jan', 'feb', 'mar'];

    protected $columns = [
        "year" => "Financial Year",
        'u_id' => "SR code",
        'ar_id' => "Area Code",
        'product_id' => "Product code",
        'price' => 'Budget Price',
        "apr_qty" => "Apr Qty",
        "may_qty" => "May Qty",
        "jun_qty" => "Jun Qty",
        "jul_qty" => "Jul Qty",
        "aug_qty" => "Aug Qty",
        "sep_qty" => "Sep Qty",
        "oct_qty" => "Oct Qty",
        "nov_qty" => "Nov Qty",
        "dec_qty" => "Dec Qty",
        "jan_qty" => "Jan Qty",
        "feb_qty" => "Feb Qty",
        "mar_qty" => "Mar Qty",
    ];

    protected $tips = [];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'u_id':
                if (!$value)
                    throw new WebAPIException("Please provide a user code!");
                $user = User::where((new User)->getCodeName(), "LIKE", $value)->first();
                if (!$user)
                    throw new WebAPIException("User not found! Given user code is '$value'");
                return $user->getKey();
            case 'ar_id':
                if (!$value)
                    throw new WebAPIException("Please provide a Area code!");
                $area = Area::where((new Area)->getCodeName(), "LIKE", $value)->first();
                if (!$area)
                    throw new WebAPIException("Area not found! Given area code is '$value'");
                return $area->getKey();
            case 'product_id':
                if ($value) {
                    $product = Product::where((new Product)->getCodeName(), "LIKE", $value)->first();
                    if (!$product)
                        throw new WebAPIException("Product not found! Given code is '$value'");
                    return $product->getKey();
                } else {
                    return null;
                }
            case "year":
                if (!is_numeric($value))
                    throw new WebAPIException("Please provide a financial error");
                return $value;
            default:
                return ($value <= 0 || !$value) ? null : $value;
        }
    }

    protected function insertRow($row)
    {
        // $year = date('m') < 4 ? date('Y') - 1 : date('Y');
        $year = $row['year'];
        foreach ($this->monthNames as $key => $month) {

            if (isset($row[$month . "_qty"]) && \is_numeric($row[$month . "_qty"]) && $row[$month . "_qty"] > 0) {

                if ($this->lastUser == $row['u_id']) {
                    $latestTarget = SfaTarget::where('u_id', $row['u_id'])
                        ->where('trg_month', $key > 8 ? $key - 8 : $key + 4)
                        ->where('trg_year', $key > 8 ? $year + 1 : $year)
                        ->latest()
                        ->first();

                    if (!$latestTarget) {
                        $latestTarget = SfaTarget::create([
                            'u_id' => $row['u_id'],
                            'ar_id' => $row['ar_id'],
                            'trg_month' => $key > 8 ? $key - 8 : $key + 4,
                            'trg_year' => $key > 8 ? $year + 1 : $year
                        ]);
                    }
                } else {
                    $latestTarget = SfaTarget::create([
                        'u_id' => $row['u_id'],
                        'ar_id' => $row['ar_id'],
                        'trg_month' => $key > 8 ? $key - 8 : $key + 4,
                        'trg_year' => $key > 8 ? $year + 1 : $year
                    ]);
                }


                SfaTargetProduct::create([
                    'sfa_trg_id' => $latestTarget->getKey(),
                    'budget_price' => $row['price'],
                    'stp_amount' => $row[$month . '_qty'] * $row['price'],
                    'stp_qty' => $row[$month . "_qty"],
                    'product_id' => $row['product_id']
                ]);
            }
        }

        if ($this->lastUser != $row['u_id']) {
            $this->lastUser = $row['u_id'];
        }
    }
}
