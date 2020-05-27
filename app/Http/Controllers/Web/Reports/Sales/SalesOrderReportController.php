<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\SfaSalesOrder;
use App\Models\SfaSalesOrderProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SalesOrderReportController extends ReportController {
    protected $title = "Sales Order Report";

    public function search($request){

        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $values = $request->input('values');

        $query = SfaSalesOrder::with(['user','chemist','salesOrderProducts','salesOrderProducts.product','area']);

        // $query->where('order_date','>=',$values['s_date']);
        // $query->where('order_date','<=',$values['e_date']);

        $query->whereBetween( DB::raw( 'DATE(order_date)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);


        if(isset($values['order_number'])){
            $query->where('order_no','LIKE',"%{$values['order_number']}%");
        }

        if(isset($values['user'])){
            $query->where('u_id',$values['user']['value']);
        }

        if(isset($values['ar_id'])){
            $query->where('ar_id',$values['ar_id']['value']);
        }

        if(isset($values['chemist'])){
            $query->where('chemist_id',$values['chemist']['value']);
        }

        if(isset($values['order_mode'])){
            $query->where('order_mode',$values['order_mode']['value']);
        }

        if(isset($values['s_date']) && isset($values['e_date'])){
            // $query->whereDate('order_date','>=',$request->input('values.s_date',date('Y-m-d')));
            // $query->whereDate('order_date','<=',$request->input('values.e_date',date('Y-m-d')));

            $query->whereBetween( DB::raw( 'DATE(order_date)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        if($user->getRoll() == config('shl.area_sales_manager_type')){
            if(isset($area->ar_code)){
                $users = User::where('u_code','LIKE','%'.$area->ar_code.'%')->get();
                $query->whereIn('u_id',$users->pluck('id')->all());
            }
        }

        $grandtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('sales_order_amt'));

        $count = $this->paginateAndCount($query,$request,'order_no');

        $results = $query->get();

        // $results->filter(function ($value, $key) {
        //     return $value->order_id == 28115;
        // });

        // return $results;

        $results->transform(function(SfaSalesOrder $salesOrder){

            return [
                'order_no'=>$salesOrder->order_no,
                'contract'=>$salesOrder->contract,
                'u_id'=>$salesOrder->user?[
                    'value'=>$salesOrder->user->getKey(),
                    'label'=>$salesOrder->user->name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'chemist_id'=>$salesOrder->chemist?[
                    'value'=>$salesOrder->chemist->getKey(),
                    'label'=>$salesOrder->chemist->chemist_name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'order_date'=>$salesOrder->order_date,
                'latitude'=>$salesOrder->latitude,
                'longitude'=>$salesOrder->longitude,
                'battery_lvl'=>$salesOrder->battery_lvl,
                'app_version'=>$salesOrder->app_version,
                'integrated_at'=>$salesOrder->integrated_at?'Integrated':'Not integrated',
                'details'=>[
                    'title'=>$salesOrder->order_no,
                    'products'=>$salesOrder->salesOrderProducts->map(function(SfaSalesOrderProduct $salesOrderProduct){

                        return [
                            'product'=>$salesOrderProduct->product?[
                                'value'=>$salesOrderProduct->product->getKey(),
                                'label'=>$salesOrderProduct->product->product_name
                            ]:[
                                'value'=>0,
                                'label'=>"DELETED"
                            ],
                            'qty'=>$salesOrderProduct->sales_qty
                        ];
                    })
                ],
                'amount_unformed' =>$salesOrder->sales_order_amt,
                'amount'=> number_format($salesOrder->sales_order_amt,2),
            ];
        });
        $newRow = [];
        $newRow  = [
            'special' => true,
            'order_no' =>'Total',
            'contract'=>NULL,
            'u_id'=>NULL,
            'chemist_id'=>NULL,
            'order_date'=>NULL,
            'latitude'=>NULL,
            'longitude'=>NULL,
            'battery_lvl'=>NULL,
            'app_version'=>NULL,
            'integrated_at'=>NULL,
            'details'=>NULL,
            'amount' => number_format($results->sum('amount_unformed'),2)
        ];

        $newGrand = [];
        $newGrand  = [
            'special' => true,
            'order_no' =>'Grand Total',
            'contract'=>NULL,
            'u_id'=>NULL,
            'chemist_id'=>NULL,
            'order_date'=>NULL,
            'latitude'=>NULL,
            'longitude'=>NULL,
            'battery_lvl'=>NULL,
            'app_version'=>NULL,
            'integrated_at'=>NULL,
            'details'=>NULL,
            'amount' => number_format($grandtot, 2)
        ];

        $results->push($newRow);
        $results->push($newGrand);

        return [
            'results'=>$results,
            'count'=>$count
        ];

    }

    public function setColumns(ColumnController $columnController, Request $request){

        $columnController->text('order_no')->setLabel('Order No.');
        $columnController->text('contract')->setLabel('Contract');
        $columnController->ajax_dropdown('u_id')->setLabel('User');
        $columnController->ajax_dropdown('chemist_id')->setLabel('Chemist');
        $columnController->date('order_date')->setLabel('Date');
        $columnController->text('latitude')->setLabel('Latitude');
        $columnController->text('longitude')->setLabel('Longitude');
        $columnController->text('battery_lvl')->setLabel('Battery Level');
        $columnController->text("app_version")->setLabel("App Version");
        $columnController->text('integrated_at')->setLabel('Integrated Status');
        $columnController->custom("details")->setLabel("Products")->setComponent('SalesOrderDetails');
        $columnController->number("amount")->setLabel("Amount");
    }

    public function setInputs($inputController){
        $inputController->text('order_number')->setLabel('Order Number')->setValidations('');
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=>config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('chemist')->setLabel('Chemist')->setLink('chemist')->setValidations('');
        $inputController->date('s_date')->setLabel("From")->setValidations('');
        $inputController->date('e_date')->setLabel("To")->setValidations('');
        $options = [
            0=>'Planned',
            1=>'UnPlanned'
        ];
        $inputController->select('order_mode')->setLabel('Order Mode')->setOptions($options);

        $inputController->setStructure([['user','chemist','order_number'],['order_mode','ar_id','s_date','e_date']]);
    }

}
