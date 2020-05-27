<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorStock;
use App\Models\StockAdjusment;
use App\Models\StockAdjusmentProduct;
use App\Models\StockWriteOff;
use App\Models\StockWriteOffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjusmentController extends Controller
{

    public function loadAdjNo(Request $request)
    {
        $dis_id = $request->input('dis_id.value');

        $stock_adjuest = StockAdjusment::query();
        $stock_max = $stock_adjuest->count('stk_adj_no');

        $stock_off = StockWriteOff::query();
        $stock_off_max = $stock_off->count('wo_no');

        if (isset($stock_max)) {
            if ($request->number == 1) {
                $adjnumber = 'ADJ/' . $dis_id . '/' . str_pad($stock_max + 1, 5, 0, STR_PAD_LEFT);
            }
        } else {
            if ($request->number == 1) {
                $adjnumber = 'ADJ/' . $dis_id . '/1';
            }
        }

        if (isset($stock_off_max)) {
            if ($request->number == 2) {
                $adjnumber = 'WO/' . $dis_id . '/' . str_pad($stock_off_max + 1, 5, 0, STR_PAD_LEFT);
            }
        } else {
            if ($request->number == 2) {
                $adjnumber = 'WO/' . $dis_id . '/1';
            }
        }

        return [
            'number' => $adjnumber,
        ];

    }

    public function loadData(Request $request)
    {

        $query = DB::table('distributor_stock as ds')
            ->select(
                'p.product_id',
                'p.product_name',
                'ds.db_id',
                'db.db_code',
                DB::raw('SUM(ds.ds_credit_qty - ds.ds_debit_qty) as qty')
            )
            ->join('product as p', 'p.product_id', 'ds.product_id')
            ->join('distributor_batches as db', 'db.db_id', 'ds.db_id')
        // ->join('distributor_batches as db','db.product_id','ds.product_id')
            ->where('ds.dis_id', $request->input('data.value'))
            // ->groupBy('db.db_id');
            ->groupBy('ds.product_id', 'db.db_id');
        $results = $query->get();

        $results->transform(function ($val, $key) use ($results) {
            $count = $results->where('product_id', $val->product_id)->count();

            if ($key) {

                $pro_id = $val->product_id;
                $preRow = $results[$key - 1];
                $pre_pro_id = $preRow->product_id;

                if ($pro_id != $pre_pro_id) {
                    $return['pro_name'] = $val->product_name;
                    $return['pro_name_rowspan'] = $count;
                } else {
                    $return['pro_name'] = null;
                    $return['pro_name_rowspan'] = 0;
                }
            } else {
                $return['pro_name'] = $val->product_name;
                $return['pro_name_rowspan'] = $count;
            }

            // $return['no'] = $key + 1;
            $return['pro_id'] = $val->product_id;
            $return['pro_name'] = $val->product_name;
            $return['ava_qty'] = $val->qty;
            $return['bt_id'] = $val->db_code;
            $return['batch'] = $val->db_id;
            $return['aju_qty'] = 0;
            $return['reason'] = "";
            return $return;
        });

        return [
            'results' => $results,
        ];
    }

    public function saveData(Request $request)
    {
        $data = $request->input('data');
        $dis_id = $request->input('dis_id.value');
        $type = $request->input('type.value');
        $adjNumber = $request->input('adjNumber');

        // return $data;die;
        $user = Auth::user();

        if ($type == 1) {

            try {
                DB::beginTransaction();

                $stock_adj = StockAdjusment::create([
                    'stk_adj_no' => $adjNumber,
                    'dis_id' => $dis_id,
                    'stk_adj_date' => date('Y-m-d'),
                    'ajust_u_id' => $user->getKey(),
                ]);

                $inserted = false;
                foreach ($data as $key => $val) {

                    if ($val['aju_qty']) {
                        $adj = StockAdjusmentProduct::create([
                            'stk_adj_id' => $stock_adj->getKey(),
                            'product_id' => $val['pro_id'],
                            'db_id' => $val['batch'],
                            'stk_adj_qty' => $val['aju_qty'],
                            'reason' => $val['reason'] ? $val['reason'] : null,
                        ]);

                        DistributorStock::create([
                            'dis_id' => $dis_id,
                            'product_id' => $val['pro_id'],
                            'db_id' => $val['batch'],
                            'ds_credit_qty' => $val['aju_qty'],
                            'ds_debit_qty' => 0,
                            'ds_ref_id' => $adj->getKey(),
                            'ds_ref_type' => 3,
                        ]);
                        $inserted = true;
                    }
                }

                if (!$inserted) {
                    throw new WebAPIException('You have to Ajust at least one product');
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } else if ($type == 2) {

            try {
                DB::beginTransaction();

                $stock_off = StockWriteOff::create([
                    'wo_no' => $adjNumber,
                    'dis_id' => $dis_id,
                    'wo_date' => date('Y-m-d'),
                    'write_off_u_id' => $user->getKey(),
                ]);

                $inserted = false;
                foreach ($data as $key => $val) {

                    if ($val['aju_qty']) {
                        $write = StockWriteOffProduct::create([
                            'wo_id' => $stock_off->getKey(),
                            'product_id' => $val['pro_id'],
                            'db_id' => $val['batch'],
                            'wo_qty' => $val['aju_qty'],
                            'reason' => $val['reason'] ? $val['reason'] : null,
                        ]);

                        DistributorStock::create([
                            'dis_id' => $dis_id,
                            'product_id' => $val['pro_id'],
                            'db_id' => $val['batch'],
                            'ds_credit_qty' => 0,
                            'ds_debit_qty' => $val['aju_qty'],
                            'ds_ref_id' => $write->getKey(),
                            'ds_ref_type' => 4,
                        ]);
                        $inserted = true;
                    }

                }

                if (!$inserted) {
                    throw new WebAPIException('You have to Ajust at least one product');
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return [
            'results' => true,
            'message' => $adjNumber . " Stock Ajusment Successfully Saved",
        ];
    }
}
