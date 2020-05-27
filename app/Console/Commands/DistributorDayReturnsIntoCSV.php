<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportDistributorReturns;

class DistributorDayReturnsIntoCSV extends Command
{
        /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:distributor_day_returns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make distributor wise return information csv';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

        /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Fetching information");

        try{

            $this->info("Fetching sales information for ". date('Y-m-01')." to ". date('Y-m-d'));

            $returns = DB::table('distributor_return as rn')
            ->join('distributor_customer as dc','dc.dc_id', 'rn.dc_id')
            ->join('distributor_return_item as drp','drp.dis_return_id' , 'rn.dis_return_id')
            ->join('product as p','p.product_id' , 'drp.product_id')
            ->join('sub_town as st', 'st.sub_twn_id','dc.sub_twn_id')
            ->join('town as t','st.twn_id','t.twn_id')
            ->join('area as a','a.ar_id','t.ar_id')
            ->join('principal as pr','pr.principal_id','p.principal_id')
            ->join('users as sr','sr.id','rn.dsr_id')
            ->join('users as ds','ds.id','rn.dis_id')
            ->leftJoin('tax_codes AS tc', 'tc.tax_code_id', 'p.tax_code_id')
            ->select([
                DB::raw('"DISTY" as Unit'),
                DB::raw('"RETURN" as DocType'),
                'ds.u_code AS TerritoryCode',
                'st.sub_twn_code AS DelivaryTown',
                'pr.principal_code AS Supplier',
                'ds.u_code AS LocationCode',
                'rn.dist_return_number AS CreditNoteNo',
                'rn.dist_return_number AS CustomerOrderReference',
                'rn.dist_return_number AS OrderNumber',
                DB::raw('DATE(rn.created_at) as CreditDate'),
                'dc.dc_code AS RetailerCode',
                'sr.u_code AS ExecutiveCode',
                'p.product_code AS ProductCode',
                'drp.dri_price AS UnitPrice',
                DB::raw('(drp.dri_qty * -1) as ReturnQty'),
                DB::raw('"0" as ReturnBonusQty'),
                DB::raw('((drp.dri_price * drp.dri_qty) * -1) as CreditLineGoodsValue'),
                DB::raw('(ROUND(((drp.dri_price * drp.dri_qty) / 100) * drp.dri_dis_percent,2) * -1) as CreditLineDiscountValue'),
                DB::raw('(ROUND(((drp.dri_price * drp.dri_qty) / 100) * IFNULL(tc.fee_rate,0),2) * -1) as CreditLineVatValue'),
                DB::raw('((drp.dri_price * drp.dri_qty) * -1) as CreditLineGrsValue')
            ])
            ->whereNull('rn.deleted_at')
            ->whereNull('dc.deleted_at')
            ->whereNull('drp.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereBetween(DB::raw('DATE(rn.created_at)'),[date('Y-m-01'),date('Y-m-d')])
            ->get();

            $returnBonus = DB::table('distributor_return as rn')
            ->join('distributor_customer as dc','dc.dc_id', 'rn.dc_id')
            ->join('distributor_return_bonus_item as drp','drp.dis_return_id' , 'rn.dis_return_id')
            ->join('product as p','p.product_id' , 'drp.product_id')
            ->join('sub_town as st', 'st.sub_twn_id','dc.sub_twn_id')
            ->join('town as t','st.twn_id','t.twn_id')
            ->join('area as a','a.ar_id','t.ar_id')
            ->join('principal as pr','pr.principal_id','p.principal_id')
            ->join('users as sr','sr.id','rn.dsr_id')
            ->join('users as ds','ds.id','rn.dis_id')
            ->join('distributor_batches as db','db.db_id','drp.db_id')
            ->select([
                DB::raw('"DISTY" as Unit'),
                DB::raw('"RETURN" as DocType'),
                'ds.u_code AS TerritoryCode',
                'st.sub_twn_code AS DelivaryTown',
                'pr.principal_code AS Supplier',
                'ds.u_code AS LocationCode',
                'rn.dist_return_number AS CreditNoteNo',
                'rn.dist_return_number AS CustomerOrderReference',
                'rn.dist_return_number AS OrderNumber',
                DB::raw('DATE(rn.created_at) as CreditDate'),
                'dc.dc_code AS RetailerCode',
                'sr.u_code AS ExecutiveCode',
                'p.product_code AS ProductCode',
                'db.db_price AS UnitPrice',
                DB::raw('"0" as ReturnQty'),
                DB::raw('(drp.drbi_qty * -1) as ReturnBonusQty'),
                DB::raw('"0" as CreditLineGoodsValue'),
                DB::raw('"0" as CreditLineDiscountValue'),
                DB::raw('"0" as CreditLineVatValue'),
                DB::raw('"0" as CreditLineGrsValue')
            ])
            ->whereNull('rn.deleted_at')
            ->whereNull('dc.deleted_at')
            ->whereNull('drp.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereBetween(DB::raw('DATE(rn.created_at)'),[date('Y-m-01'),date('Y-m-d')])
            ->get();

            $this->info("Fetched ".$returns->count().' return rows');
            $this->info("Fetched ".$returnBonus->count().' return bonus rows');

            $data['title']= 'Distributor Return Details';

            $headers['Unit'] = 'Unit';
            $headers['DocType'] = 'DocType';
            $headers['TerritoryCode'] = 'TerritoryCode';
            $headers['DelivaryTown'] = 'DelivaryTown';
            $headers['Supplier'] = 'Supplier';
            $headers['LocationCode'] = 'LocationCode';
            $headers['CreditNoteNo'] = 'CreditNoteNo';
            $headers['CustomerOrderReference'] = 'CustomerOrderReference';
            $headers['OrderNumber'] = 'OrderNumber';
            $headers['CreditDate'] = 'CreditDate';
            $headers['RetailerCode'] = 'RetailerCode';
            $headers['ExecutiveCode'] = 'ExecutiveCode';
            $headers['ProductCode'] = 'ProductCode';
            $headers['UnitPrice'] = 'UnitPrice';
            $headers['ReturnQty'] = 'ReturnQty';
            $headers['ReturnBonusQty'] = 'ReturnBonusQty';
            $headers['CreditLineGoodsValue'] = 'CreditLineGoodsValue';
            $headers['CreditLineDiscountValue'] = 'CreditLineDiscountValue';
            $headers['CreditLineVatValue'] = 'CreditLineVatValue';
            $headers['CreditLineGrsValue'] = 'CreditLineGrsValue';

            $data['headers'] = (array)$headers;
            $data['returns'] = $returns;
            $data['returnBonus'] = $returnBonus;

            Excel::store(new ExportDistributorReturns($data), 'public/csv/distributor_wise_returns.csv');

            $this->info("CSV file is created");

        } catch (\Exception $exception){
            Storage::put('/public/errors/integration/'.date("Y-m-d").'-sd.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            throw $exception;
        }
    }
}
