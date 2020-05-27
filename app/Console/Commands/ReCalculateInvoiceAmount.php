<?php

namespace App\Console\Commands;

use App\Models\DistributorInvoice;
use App\Models\DistributorReturn;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ReCalculateInvoiceAmount extends IntegrationDateRange
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales_data:recalculate {from_date?} {to_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculating invoice amounts with latest prices.';

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

        $fromDate = $this->argument('from_date');
        $toDate = $this->argument('to_date');

        $invoiceQuery = DistributorInvoice::with('lines');

        if($fromDate&&$toDate){
            $invoiceQuery->whereDate('created_at','>=',$fromDate);
            $invoiceQuery->whereDate('created_at','<=',$toDate);
        }

        /** @var DistributorInvoice[]|Collection  */
        $invoices = $invoiceQuery->get();


        foreach ($invoices as $key => $invoice) {
            
            $totalAmount = 0;
            $totalDiscount = 0;

            $this->info("Invoice:- ".$invoice->getKey());

            foreach ($invoice->lines as $key => $line) {
                $price = Product::getPriceForDistributor($line->product_id,$line->db_id);
                $notVatPrice = Product::getNotVatPriceForDistributor($line->product_id,$line->db_id);

                $amount = $line->dil_qty * $price;
                $totalDiscount += $amount * $line->dil_discount_percent/100;

                $totalAmount+= $amount;

                $line->dil_unit_price = $price;
                $line->unit_price_no_tax = $notVatPrice;

                $line->save();
            }

            $invoice->di_amount = $totalAmount;
            $invoice->di_discount = $totalDiscount;

            $invoice->save();
        }


        $returnQuery = DistributorReturn::with('lines');

        if($fromDate&&$toDate){
            $returnQuery->whereDate('created_at','>=',$fromDate);
            $returnQuery->whereDate('created_at','<=',$toDate);
        }

        /** @var DistributorReturn[]|Collection  */
        $returns = $returnQuery->get();


        foreach ($returns as $key => $return) {
            

            $this->info("Return:- ".$return->getKey());

            foreach ($return->lines as $key => $line) {
                $price = Product::getPriceForDistributor($line->product_id,$line->db_id);
                $notVatPrice = Product::getNotVatPriceForDistributor($line->product_id,$line->db_id);
                $amount = $line->dri_qty * $price;


                $line->dri_price = $price;
                $line->unit_price_no_tax = $notVatPrice;

                $line->save();
            }

        }
        
    }

}
