<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportDistributorSales;

class DistributorDaySalesIntoCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:distributor_day_sale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make distributor wise day sales information csv';

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

            $this->info("Fetching sales information for ". date('Y-m-d H:i:s'));

            $invoice = DB::table('distributor_invoice as i')
            ->join('distributor_customer as dc','dc.dc_id', 'i.dc_id')
            ->join('distributor_invoice_line as il','il.di_id', 'i.di_id')
            ->join('product as p','p.product_id' , 'il.product_id')
            ->join('sub_town as st', 'st.sub_twn_id','dc.sub_twn_id')
            ->join('town as t','st.twn_id','t.twn_id')
            ->join('area as a','a.ar_id','t.ar_id')
            ->join('principal as pr','pr.principal_id','p.principal_id')
            ->leftJoin('distributor_sales_order as so','so.dist_order_id','i.dist_order_id')
            ->join('users as sr','sr.id','i.dsr_id')
            ->join('users as ds','ds.id','i.dis_id')
            ->leftJoin('tax_codes AS tc', 'tc.tax_code_id', 'p.tax_code_id')
            ->select([
                DB::raw('"DISTY" as Unit'),
                DB::raw('"INVOICE" as DocType'),
                'ds.u_code as TerritoryCode',
                'st.sub_twn_code as DelivaryTown',
                'pr.principal_code as Supplier',
                'ds.u_code as StockTerritory',
                'i.di_number as InvoiceNo',
                'so.order_no as CustomerOrderReference',
                'so.order_no as OrderNumber',
                DB::raw('DATE(i.created_at) as InvoiceDate'),
                'dc.dc_code as RetailerCode',
                'sr.u_code as ExecutiveCode',
                'p.product_code as ProductCode',
                'il.dil_unit_price as UnitPrice',
                'il.dil_qty as InvoiceQty',
                DB::raw('"0" as BonusQty'),
                DB::raw('(il.dil_unit_price * il.dil_qty) as LineGoodsValue'),
                DB::raw('ROUND(((il.dil_unit_price * il.dil_qty) / 100) * il.dil_discount_percent,2) as LineDiscountValue'),
                DB::raw('ROUND(((il.dil_unit_price * il.dil_qty) / 100) * IFNULL(tc.fee_rate,0) ,2) as LineVatValue'),
                DB::raw('(il.dil_unit_price * il.dil_qty) - ROUND(((il.dil_unit_price * il.dil_qty) / 100) * il.dil_discount_percent,2) as LineGrsValue')
            ])
            ->whereNull('i.deleted_at')
            ->whereNull('dc.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereBetween(DB::raw('DATE(i.created_at)'),[date('Y-m-01'),date('Y-m-d')])
            ->get();

            $invoiceBonus = DB::table('distributor_invoice as i')
            ->join('distributor_customer as dc','dc.dc_id', 'i.dc_id')
            ->join('distributor_invoice_bonus_line as il','il.di_id', 'i.di_id')
            ->join('product as p','p.product_id' , 'il.product_id')
            ->join('sub_town as st', 'st.sub_twn_id','dc.sub_twn_id')
            ->join('town as t','st.twn_id','t.twn_id')
            ->join('area as a','a.ar_id','t.ar_id')
            ->join('principal as pr','pr.principal_id','p.principal_id')
            ->leftJoin('distributor_sales_order as so','so.dist_order_id','i.dist_order_id')
            ->join('users as sr','sr.id','i.dsr_id')
            ->join('users as ds','ds.id','i.dis_id')
            ->select([
                DB::raw('"DISTY" as Unit'),
                DB::raw('"INVOICE" as DocType'),
                'ds.u_code as TerritoryCode',
                'st.sub_twn_code as DelivaryTown',
                'pr.principal_code as Supplier',
                'ds.u_code as StockTerritory',
                'i.di_number as InvoiceNo',
                'so.order_no as CustomerOrderReference',
                'so.order_no as OrderNumber',
                DB::raw('DATE(i.created_at) as InvoiceDate'),
                'dc.dc_code as RetailerCode',
                'sr.u_code as ExecutiveCode',
                'p.product_code as ProductCode',
                'il.dibl_unit_price as UnitPrice',
                DB::raw('"0" as InvoiceQty'),
                DB::raw('il.dibl_qty as BonusQty'),
                DB::raw('"0" as LineGoodsValue'),
                DB::raw('"0" as LineDiscountValue'),
                DB::raw('"0" as LineVatValue'),
                DB::raw('"0" as LineGrsValue')
            ])
            ->whereNull('i.deleted_at')
            ->whereNull('dc.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereBetween(DB::raw('DATE(i.created_at)'),[date('Y-m-01'),date('Y-m-d')])
            ->get();

            $this->info("Fetched ".$invoice->count().' invoice rows ');
            $this->info("Fetched ".$invoiceBonus->count().' invoice bonus rows');

            $data['title']= 'Distributor Sales Details';

            $headers['Unit']='Unit';
            $headers['DocType']='DocType';
            $headers['TerritoryCode']='TerritoryCode';
            $headers['DelivaryTown']='DelivaryTown';
            $headers['Supplier']='Supplier';
            $headers['StockTerritory']='StockTerritory';
            $headers['InvoiceNo']='InvoiceNo';
            $headers['CustomerOrderReference']='CustomerOrderReference';
            $headers['OrderNumber']='OrderNumber';
            $headers['InvoiceDate']='InvoiceDate';
            $headers['RetailerCode']='RetailerCode';
            $headers['ExecutiveCode']='ExecutiveCode';
            $headers['ProductCode']='ProductCode';
            $headers['UnitPrice']='UnitPrice';
            $headers['InvoiceQty']='InvoiceQty';
            $headers['BonusQty']='BonusQty';
            $headers['LineGoodsValue']='LineGoodsValue';
            $headers['LineDiscountValue']='LineDiscountValue';
            $headers['LineVatValue']='LineVatValue';
            $headers['LineGrsValue']='LineGrsValue';

            $arr = [];

            $data['headers'] = (array)$headers;
            $data['invoices'] = $invoice;
            $data['invoiceBonus'] = $invoiceBonus;


            Excel::store(new ExportDistributorSales($data), 'public/csv/distributor_wise_sales.csv');

            $this->info("CSV file is created");

        }  catch (\Exception $exception){
            Storage::put('/public/errors/integration/'.date("Y-m-d").'-sd.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            throw $exception;
        }

    }
}
