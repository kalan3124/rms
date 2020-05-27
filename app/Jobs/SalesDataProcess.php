<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

use App\Models\InvoiceLine;
use App\Models\MonthWiseAchievement;
use App\Models\Product;
use App\Models\User;
use App\Traits\Territory;
use Illuminate\Support\Facades\DB;

class SalesDataProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,Territory;
    /**
     * Sales data processing month
     *
     * @var int
     */
    protected $month;

    public $timeout = 7200; // 2 hours

    /**
     * Sales data processing year
     *
     * @var int
     */
    protected $year;

    /**
     * Logged user
     *
     * @var int
     */
    protected $loggedUser;
    /**
     * Create a new job instance.
     *
     * @param string $month
     * @return void
     */
    public function __construct(int $user,int $year,int $month)
    {
        $this->loggedUser = $user;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereIn('u_tp_id',[config('shl.medical_rep_type'),config('shl.field_manager_type'),config('shl.product_specialist_type')])->get();
        $count = User::whereIn('u_tp_id',[config('shl.medical_rep_type'),config('shl.field_manager_type'),config('shl.product_specialist_type')])->count();

        $year = $this->year;
        $month = $this->month;

        $date = strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-15');

        try {

            DB::beginTransaction();

            MonthWiseAchievement::where('mwa_year',$year)->where('mwa_month',$month)->forceDelete();

            foreach ($users as $userKey => $user) {
                try {
                    $towns = $this->getAllocatedTerritories($user);
                    $products = Product::getByUser($user);

                    $this->logStatus('running','Processing Invoices. | User:- '.$user->u_code.' | Towns:- '.$towns->count().'| Products:- '.$products->count(),floor(100*(($userKey+1)/$count)));

                    $invoices = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),DB::raw('sau.id'))
                        ->join('product AS p','il.product_id','=','p.product_id')
                        ->join('chemist AS c','c.chemist_id','il.chemist_id')
                        ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
                        ->leftJoin('latest_price_informations AS pi',function($query){
                            $query->on('pi.product_id','=','p.product_id');
                            $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                                    })
                        ->select([
                            'sau.id',
                            'st.sub_twn_id',
                            'il.identity',
                            'il.product_id',
                            'p.product_code',
                            'p.product_name',
                            'p.principal_id',
                            'c.chemist_id',
                            InvoiceLine::salesQtyColumn('gross_qty',false,false),
                            InvoiceLine::salesQtyColumn('net_qty',false,false),
                            InvoiceLine::salesAmountColumn('bdgt_value'),
                            DB::raw('0 AS return_qty'),
                            DB::raw('0 AS rt_bdgt_value'),
                            DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
                            DB::raw('DAY(il.invoice_date) AS day')
                        ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all())
                        ->where('sau.id',$user->getKey())
                        ->whereIn('il.product_id',$products->pluck('product_id')->all())
                        ->whereDate('il.invoice_date','<=',date('Y-m-t',$date))
                        ->whereDate('il.invoice_date','>=',date('Y-m-01',$date))
                        ->groupBy('il.product_id','st.sub_twn_id','c.chemist_id',DB::raw('DATE(il.invoice_date)'))
                        ->get();

                    $this->logStatus('running','Processing Returns. | User:- '.$user->u_code.' | Towns:- '.$towns->count().'| Products:- '.$products->count(),floor(100*(($userKey+1)/$count)));

                    $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation( DB::table('return_lines AS rl'),DB::raw('sau.id'),true)
                        ->join('product AS p','rl.product_id','=','p.product_id')
                        ->join('chemist AS c','c.chemist_id','rl.chemist_id')
                        ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
                        ->leftJoin('latest_price_informations AS pi',function($query){
                            $query->on('pi.product_id','=','p.product_id');
                            $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                                                    })
                        ->select([
                            'sau.id',
                            'st.sub_twn_id',
                            'rl.identity',
                            'rl.product_id',
                            'p.product_code',
                            'p.product_name',
                            'p.principal_id',
                            'c.chemist_id',
                            InvoiceLine::salesQtyColumn('return_qty',true,false),
                            InvoiceLine::salesAmountColumn('rt_bdgt_value',true,false),
                            DB::raw('0 AS gross_qty'),
                            DB::raw('0 AS net_qty'),
                            DB::raw('0 AS bdgt_value'),
                            DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
                            DB::raw('DAY(rl.invoice_date) AS day')
                        ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all(),true)
                        ->where('sau.id',$user->getKey())
                        ->whereIn('rl.product_id',$products->pluck('product_id')->all())
                        ->whereDate('rl.invoice_date','<=',date('Y-m-t',$date))
                        ->whereDate('rl.invoice_date','>=',date('Y-m-01',$date))
                        ->groupBy('rl.product_id','st.sub_twn_id','c.chemist_id',DB::raw('DATE(rl.invoice_date)'))
                        ->get();
                    $results = $invoices->concat($returns);

                    $this->logStatus('running','Saving | User:- '.$user->u_code.' | Towns:- '.$towns->count().'| Products:- '.$products->count(),floor(100*(($userKey+1)/$count)));

                    foreach ($results as $key => $result) {
                        MonthWiseAchievement::create([
                            'u_id'=>$user->getKey(),
                            'product_id'=>$result->product_id,
                            'chemist_id'=>$result->chemist_id,
                            'sub_twn_id'=>$result->sub_twn_id,
                            'mwa_year'=>$year,
                            'mwa_month'=>$month,
                            'mwa_day'=>$result->day,
                            'mwa_sales_allocation'=>0,
                            'mwa_qty'=>((float)$result->net_qty) - ((float)$result->return_qty),
                            'mwa_amount'=>((float) $result->bdgt_value) - ((float)$result->rt_bdgt_value)
                        ]);
                    }


                } catch (\Exception $e){
                }

            }

            $this->logStatus("running","Finished",100);

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            $this->logStatus('error',$e->__toString(),100);
            // throw $e;
        }

    }

    public function failed($exception)
    {
        Storage::put('/public/errors/'.date("Y-m-d").'.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");

        $this->logStatus('error','Server Error Apeared!',0);
    }

    public function logStatus($status,$message,$progress=0){


        $content = json_encode([
            'status'=>$status,
            'message'=>$message,
            'percentage'=>$progress
        ]);

        $name = '/sales_data_progress/'.$this->loggedUser.'.json';

        Storage::put($name,$content);

        return true;
    }
}
