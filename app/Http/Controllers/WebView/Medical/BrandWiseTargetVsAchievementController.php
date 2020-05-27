<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\UserTarget;
use App\Traits\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrandWiseTargetVsAchievementController extends Controller {
    use Territory;

    public function index(){
        return view('WebView/Medical.brand_wise_target');
    }

    public function search(Request $request){

        $date = $request->input('date_month');

        if(!$date){
            $date = date('Y-m-d');
        }

        $date = strtotime($date);

        $user = Auth::user();

        $products = Product::getByUserForSales($user);

        $brandIds = $products->pluck('brand_id')->all();

        /** @var Brand[] $brands */
        $brands = Brand::whereIn('brand_id',$brandIds)->get();

        /** @var UserTarget $target */
        $target = UserTarget::where('u_id',$user->getKey())
            ->where('ut_year',date('Y',$date))
            ->where('ut_month',date('m',$date))
            ->with(['userProductTargets'])
            ->latest()
            ->first();

        $towns = $this->getAllocatedTerritories($user);


        $invoiceAmounts = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),DB::raw('sau.id'))
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','il.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'p.brand_id',
                InvoiceLine::salesAmountColumn('amount')
            ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all())
            ->where('sau.id',$user->getKey())
            ->whereIn('il.product_id',$products->pluck('product_id')->all())
            ->whereDate('il.invoice_date','<=',date('Y-m-t',$date))
            ->whereDate('il.invoice_date','>=',date('Y-m-01',$date))
            ->groupBy('p.brand_id')
            ->get();

        $returnAmounts = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation( DB::table('return_lines AS rl'),DB::raw('sau.id'),true)
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','rl.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
                'p.brand_id',
                InvoiceLine::salesAmountColumn('amount',true)
            ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all(),true)
            ->where('sau.id',$user->getKey())
            ->whereIn('rl.product_id',$products->pluck('product_id')->all())
            ->whereDate('rl.invoice_date','<=',date('Y-m-t',$date))
            ->whereDate('rl.invoice_date','>=',date('Y-m-01',$date))
            ->groupBy('p.brand_id')
            ->get();

        $brands->transform(function(Brand $brand)use($products,$target,$invoiceAmounts,$returnAmounts){
            $brandProducts = $products->where('brand_id',$brand->brand_id);
            $targetValue = 0;
            if($target){
                $brandTargets = $target->userProductTargets->whereIn('product_id',$brandProducts->pluck('product_id'));
                $targetValue =$brandTargets->sum('upt_value');
            }

            $invoiceAmount = $invoiceAmounts->where('brand_id',$brand->getKey())->first();
            $invoiceAmount = $invoiceAmount?$invoiceAmount->amount:0;

            $returnAmount = $returnAmounts->where('brand_id',$brand->getKey())->first();
            $returnAmount = $returnAmount?$returnAmount->amount:0;

            return [
                'brand_name'=>$brand->brand_name,
                'target_value'=>$targetValue,
                'ach_value'=> number_format($invoiceAmount-$returnAmount,2),
                'ach_percent'=>number_format($targetValue?($invoiceAmount-$returnAmount)/$targetValue:0,2)
            ];

        });

        return view('WebView/Medical.brand_wise_target_results',[
            'products'=>$brands
        ]);
    }
}
