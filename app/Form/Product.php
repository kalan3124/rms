<?php 
namespace App\Form;

use App\Exceptions\WebAPIException;
use App\Models\DsrProduct;
use App\Models\InvoiceLine;
use App\Models\TeamProduct;
use App\Models\User;
use App\Models\UserTeam;
use App\Traits\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Product extends Form{

    use Team;

    protected $title = 'Product';

    protected $dropdownDesplayPattern = 'product_name';

    public function beforeSearch($query,$values){
        $query->with('brand','product_family','principal','division');

        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $products = $this->getProductsByUser($user);

            $query->whereIn('product_id',$products->pluck('product_id')->all());
        }
    }

    public function beforeDropdownSearch($query, $keyword)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type'),
            config('shl.distributor_sales_rep_type')
        ])){
            $products = $this->getProductsByUser($user);

            $query->whereIn('product_id',$products->pluck('product_id')->all());
        } 

        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $products = TeamProduct::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('product_id',$products->$users->pluck('product_id')->all());
        }   
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('product_code')->setLabel('Product Code')->isUpperCase();
        $inputController->text('product_name')->setLabel('Product Name')->isUpperCase();
        $inputController->ajax_dropdown('brand_id')->setLabel('Brand Name')->setLink('brand');
        $inputController->ajax_dropdown('product_family_id')->setLabel('Product Family')->setLink('product_family');
        $inputController->ajax_dropdown('principal_id')->setLabel('Principal Name')->setLink('principal');
        $inputController->ajax_dropdown('divi_id')->setLabel('division')->setLink('division');

        $inputController->setStructure([
            'product_code',
            'product_name',
            ['principal_id','product_family_id'],
            ['brand_id','divi_id']
        ]);
    }

    public function beforeDelete($inst)
    {
        if($inst){
            $invoice = InvoiceLine::where('product_id',$inst->getKey())->first();

            if($invoice){
                throw new WebAPIException("You can not delete this product. This product was sold by some SRs. Please contact your system administrator to delete this product.");
            }
        }

        return true;
    }

    public function filterDropdownSearch($query, $where)
    {
        if(isset($where['dsr_id'])){
            $dsrId = $where['dsr_id'];
            if(is_array($dsrId))
                $dsrId = $where['dsr_id']['value'];

            $products = $this->getProductsByUser( User::find( $dsrId));

            $query->whereIn('product_id',$products->pluck('product_id')->all());
            
            unset($where['dsr_id']);
        }

        if(isset($where['dis_id'])){
            $disId = $where['dis_id'];
            if(is_array($disId))
                $disId = $where['dis_id']['value'];

            $stockProducts = DB::table('distributor_stock AS ds')
            ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
            ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.product_id'])
            ->where('ds.dis_id',$disId)
            ->whereDate('db.db_expire','>=',date('Y-m-d'))
            ->groupBy('db.db_id','db.product_id')
            ->orderBy('db.db_expire')
            ->having('stock','>','0')
            ->get();

            $query->whereIn('product_id',$stockProducts->pluck('product_id')->all());

            unset($where['dis_id']);
        }
    }
}