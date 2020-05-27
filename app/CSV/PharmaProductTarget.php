<?php

namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\User;
use App\Models\Product;
use App\Models\UserTarget;
use App\Models\UserProductTarget;

class PharmaProductTarget extends Base
{
    protected $title = "Pharma Product Targets";

    protected $lastUser = 0;

    protected $monthNames = ['apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'jan', 'feb', 'mar'];

    protected $columns = [
        "year" => "Financial Year",
        'u_id' => "Employee code",
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
                return ($value < 0 || !isset($value)) ? null : $value;
        }
    }

    protected function insertRow($row)
    {
        $year = $row['year'];
        foreach ($this->monthNames as $key => $month) {

            if (isset($row[$month . "_qty"]) && \is_numeric($row[$month . "_qty"]) && $row[$month . "_qty"] >= 0) {

                if ($this->lastUser == $row['u_id']) {
                    $latestTarget = UserTarget::where('u_id', $row['u_id'])
                        ->where('ut_month', $key > 8 ? $key - 8 : $key + 4)
                        ->where('ut_year', $key > 8 ? $year + 1 : $year)
                        ->latest()
                        ->first();

                    if (!$latestTarget) {
                        $latestTarget = UserTarget::create([
                            'u_id' => $row['u_id'],
                            'ut_month' => $key > 8 ? $key - 8 : $key + 4,
                            'ut_year' => $key > 8 ? $year + 1 : $year,
                            'ut_qty' => 0,
                            'ut_value' => 0
                        ]);
                    }
                } else {
                    $latestTarget = UserTarget::create([
                        'u_id' => $row['u_id'],
                        'ut_month' => $key > 8 ? $key - 8 : $key + 4,
                        'ut_year' => $key > 8 ? $year + 1 : $year,
                        'ut_qty' => 0,
                        'ut_value' => 0
                    ]);
                }

                UserProductTarget::where('ut_id', $latestTarget->getKey())->where('product_id', $row['product_id'])->delete();

                UserProductTarget::create([
                    'ut_id' => $latestTarget->getKey(),
                    'upt_value' => $row[$month . '_qty'] * $row['price'],
                    'upt_qty' => $row[$month . "_qty"],
                    'product_id' => $row['product_id']
                ]);
            }
        }

        if ($this->lastUser != $row['u_id']) {
            $this->lastUser = $row['u_id'];
        }
    }
}
