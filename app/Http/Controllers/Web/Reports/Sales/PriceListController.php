<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use App\Models\SalesPriceLists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceListController extends ReportController
{

    protected $title = "Price Lists";

    public function search($request)
    {

        $query = DB::table('sales_price_list AS spl')
            ->join('product AS p', 'p.product_id', 'spl.product_id')
            ->select([
                'spl.price_list_no',
                'spl.description',
                'spl.sales_price_group_id',
                'spl.currency_code',
                'spl.catalog_no',
                'p.product_name',
                'spl.valid_from_date',
                'spl.base_price',
                'spl.sales_price',
                'spl.base_price_incl_tax',
                'spl.sales_prices_incl_tax'
            ])
            ->whereNull('spl.deleted_at')
            ->whereNull('p.deleted_at');

        if ($request->has('values.product.value')) {
            $query->where('p.product_id', $request->input('values.product.value'));
        }

        if ($request->has('values.sales_price.value')) {
            $priceList = SalesPriceLists::find($request->input('values.sales_price.value'));


            $query->where('spl.price_list_no', $priceList->price_list_no);
        }


        $sortMode = $request->input('sortMode') ?? 'desc';

        $sortBy = 'spl.price_list_no';

        switch ($request->input('sortBy')) {
            case 'description':
                $sortBy = 'spl.description';
                break;
            case 'sales_price_group_id':
                $sortBy = 'spl.sales_price_group_id';
                break;
            case 'currency_code':
                $sortBy = 'spl.currency_code';
                break;
            case 'catalog_no':
                $sortBy = 'spl.catalog_no';
                break;
            case 'product_name':
                $sortBy = 'p.product_name';
                break;
            case 'from_date':
                $sortBy = 'spl.valid_from_date';
                break;
            case 'base_price':
                $sortBy = 'spl.base_price';
                break;
            case 'sales_price':
                $sortBy = 'spl.sales_price';
                break;
            default:
                break;
        }

        $query->orderBy($sortBy, $sortMode);

        $count = $this->paginateAndCount($query, $request, $sortBy);

        $result = $query->get();

        $result->transform(function ($row) {
            return [
                'price_list_no' => $row->price_list_no,
                'description' => $row->description,
                'sales_price_group_id' => $row->sales_price_group_id,
                'currency_code' => $row->currency_code,
                'catalog_no' => $row->catalog_no,
                'product_name' => $row->product_name,
                'from_date' => $row->valid_from_date,
                'base_price' => number_format($row->base_price,2),
                'base_price_new' => $row->base_price,
                'base_price_vat' => number_format($row->base_price_incl_tax,2),
                'base_price_vat_new' => $row->base_price_incl_tax,
                'sales_price' => number_format($row->sales_price,2),
                'sales_price_new' => $row->sales_price,
                'sales_price_vat' => number_format($row->sales_prices_incl_tax,2),
                'sales_price_vat_new' => $row->sales_prices_incl_tax
            ];
        });

        $row = [
            'special' => true,
            'base_price' => number_format($result->sum('base_price_new'),2),
            'sales_price' => number_format($result->sum('sales_price_new'),2),
            'base_price_vat' => number_format($result->sum('base_price_vat_new'),2),
            'sales_price_vat' => number_format($result->sum('sales_price_vat_new'),2)
       ];

       $result->push($row);


        return [
            'results' => $result,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('price_list_no')->setLabel('Price List No.');
        $columnController->text('description')->setLabel('Description');
        $columnController->text('sales_price_group_id')->setLabel('Price Group');
        $columnController->text('currency_code')->setLabel('Currency');
        $columnController->text('catalog_no')->setLabel('Product Code');
        $columnController->text('product_name')->setLabel('Product Name');
        $columnController->text("from_date")->setLabel("Valid From Date");
        $columnController->number('base_price')->setLabel('Base Price (MRP)');
        $columnController->number('base_price_vat')->setLabel('Base Price (MRP With VAT)');
        $columnController->number('sales_price')->setLabel('Sales Price');
        $columnController->number('sales_price_vat')->setLabel('Sales Price (With VAT)');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('product')->setLabel("Product")->setLink('product');
        $inputController->ajax_dropdown('sales_price')->setLabel("Price Group")->setLink('sales_price_lists');
        $inputController->setStructure(['product', 'sales_price']);
    }

}

