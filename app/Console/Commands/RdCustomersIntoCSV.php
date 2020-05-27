<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportRDCustomers;

class RdCustomersIntoCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:rd_customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make RD Customer CSV';

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

            $query = DB::table('distributor_customer as dc')
            ->leftJoin('distributor_customer_class as dcc','dcc.dcc_id','dc.dcc_id')
            ->join('sub_town as st','st.sub_twn_id','dc.sub_twn_id')
            ->select([
                'dc.dc_code AS CustomerCode',
                'dc.dc_name AS CustomerName',
                DB::raw('"0" as CreditLimit'),
                DB::raw('"S45" as SettlementTermsCode'),
                'dcc.dcc_code AS TypeCode',
                'dcc.dcc_name AS TypeName',
                DB::raw('"RT" as GroupCode'),
                DB::raw('"Retailers" as GroupName'),
                DB::raw('"O2" as ClassCode'),
                DB::raw('"Other Class" as ClassName'),
                'st.sub_twn_code AS TownCode',
                'st.sub_twn_name AS TownName',
                DB::raw('"NULL" AS TerritoryCode'),
                DB::raw('"NULL" AS TerritoryName'),
                DB::raw('"NULL" AS DistrictName'),
                DB::raw('"NE SECONDARY" as Category'),
                DB::raw('"NULL" as IsActive'),
                DB::raw('"0" as Infered'),
                DB::raw('"2017-04-01" as ValidFrom')
            ])
            ->whereNull('dc.deleted_at')
            ->whereNull('dcc.deleted_at')
            ->whereNull('st.deleted_at')
            ->get();

            $this->info("Fetched ".$query->count().' rows');

            $data['title']= 'Distributor Customers';

            $headers['CustomerCode'] = 'CustomerCode';
            $headers['CustomerName'] = 'CustomerName';
            $headers['CreditLimit'] = 'CreditLimit';
            $headers['SettlementTermsCode'] = 'SettlementTermsCode';
            $headers['TypeCode'] = 'TypeCode';
            $headers['TypeName'] = 'TypeName';
            $headers['GroupCode'] = 'GroupCode';
            $headers['GroupName'] = 'GroupName';
            $headers['ClassCode'] = 'ClassCode';
            $headers['ClassName'] = 'ClassName';
            $headers['TownCode'] = 'TownCode';
            $headers['TownName'] = 'TownName';
            $headers['TerritoryCode'] = 'TerritoryCode';
            $headers['TerritoryName'] = 'TerritoryName';
            $headers['DistrictName'] = 'DistrictName';
            $headers['Category'] = 'Category';
            $headers['IsActive'] = 'IsActive';
            $headers['Infered'] = 'Infered';
            $headers['ValidFrom'] = 'ValidFrom';

            $data['headers'] = (array)$headers;
            $data['results'] = $query;

            Excel::store(new ExportRDCustomers($data), 'public/csv/distributor_customers.csv');

            $this->info("CSV file is created");

        } catch (\Exception $exception){
            Storage::put('/public/errors/integration/'.date("Y-m-d").'-sd.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            throw $exception;
        }
    }
}
