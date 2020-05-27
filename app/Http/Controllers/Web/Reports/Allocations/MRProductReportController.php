<?php
namespace App\Http\Controllers\Web\Reports\Allocations;

use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use App\Models\User;

class MRProductReportController extends ReportController {

    protected $title = "MR Product List";

    protected $defaultSortColumn = 'p.product_id' ;

    public function search($request){

        switch ($request->input('sortBy')) {
            case 'team_name':
               $sortBy = 't.tm_name';
                break;
            case 'team_name':
                $sortBy = 't.tm_code';
                break;
            case 'user_code':
                $sortBy = 'u.u_code';
                break;
            case 'user_name':
                $sortBy = 'u.name';
                break;
            case 'principal_code':
                $sortBy = 'pr.principal_code';
                break;
            case 'principal_name':
                $sortBy = 'pr.principal_id';
                break;
            case 'product_code':
                $sortBy = 'p.product_code';
                break;
            case 'product_name':
                $sortBy = 'p.product_id';
                break;
            default:
                $sortBy = 'tup.updated_at';
                break;
        }
        
        $query = DB::query();

        $this->makeQuery($query);

        $query->select(['u.name','u.u_code','t.tm_name','t.tm_code','p.product_name','p.product_code','pr.principal_code','pr.principal_name','tup.updated_at']);

        if($request->has('values.team.value')){
            $query->where('t.tm_id',$request->input('values.team.value'));
        }

        if($request->has('values.user.value')){
            $query->where('u.id',$request->input('values.user.value'));
        }

        if($request->has('values.principal.value')){
            $query->where('pr.principal_id',$request->input('values.principal.value'));
        }

        if($request->has('values.product.value')){
            $query->where('p.product_id',$request->input('values.product.value'));
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();

        $page = $request->input('page',1);
        $perPage = $request->input('perPage',25);
        $allocatedCount = $result->count();

        $queryUnallocated = DB::table('team_products AS tmp')
            ->select(['u.name','u.u_code','t.tm_name','t.tm_code','p.product_name','p.product_code','pr.principal_code','pr.principal_name','tmp.updated_at'])
            ->join('product AS p','p.product_id','=','tmp.product_id','inner')
            ->join('principal AS pr','pr.principal_id','p.principal_id')
            ->join('teams AS t','t.tm_id','=','tmp.tm_id')
            ->join('team_users AS tu','tu.tm_id','=','t.tm_id','inner')
            ->join('users AS u','u.id','=','tu.u_id','inner')
            ->whereNull('p.deleted_at')
            ->whereNotIn('t.tm_id',function($query){
                $query->select('t.tm_id');
                $this->makeQuery($query);
            })
            ->whereNull('t.deleted_at')
            ->whereNull('u.deleted_at')
            ->whereNull('pr.deleted_at')
            ->whereNull('tu.deleted_at')
            ->whereNull('tmp.deleted_at');

        if($request->has('values.team.value')){
            $queryUnallocated->where('t.tm_id',$request->input('values.team.value'));
        }

        if($request->has('values.user.value')){
            $queryUnallocated->where(function($query) use($request){
                $query->orWhere('u.id',$request->input('values.user.value'));
                $query->orWhere('t.fm_id',$request->input('values.user.value'));
            });
        }

        if($request->has('values.principal.value')){
            $queryUnallocated->where('pr.principal_id',$request->input('values.principal.value'));
        }

        if($request->has('values.product.value')){
            $queryUnallocated->where('p.product_id',$request->input('values.product.value'));
        }
        $oldCount = $count;

        $count += $queryUnallocated->count();

        if((($page-1)*$perPage)+$allocatedCount>=$oldCount){
            $totalPagesForOldQuery = ceil($oldCount/$perPage);
            $page = $page-$totalPagesForOldQuery;

            $queryUnallocated->orderBy($sortBy=='tup.updated_at'?'tmp.updated_at':$sortBy,$request->input('sortMode','desc'));
            
            if(!$this->isCSV){
                $queryUnallocated->take($perPage);
    
                $queryUnallocated->skip(($page-1)*$perPage);
            }            

            $unallocatedResult = $queryUnallocated->get();

            $result = $result->concat($unallocatedResult);    
        }

        $result->transform(function($row){
            return [
                'team_code'=>$row->tm_code,
                'team'=>$row->tm_name,
                'user_code'=>$row->u_code,
                'user_name'=> $row->name,
                'principal_code'=>$row->principal_code,
                'principal_name'=>$row->principal_name,
                'product_code'=>$row->product_code,
                'product_name'=>$row->product_name,
                'date'=>$row->updated_at
            ];
        });

        return [
            'results'=>$result,
            'count'=>$count
        ];
    }

    public function makeQuery($query){
        $query->from('team_user_products AS tup')
        ->join('team_products AS tmp','tmp.tmp_id','=','tup.tmp_id','inner')
        ->join('product AS p','p.product_id','=','tmp.product_id','inner')
        ->join('principal AS pr','pr.principal_id','p.principal_id')
        ->join('teams AS t','t.tm_id','=','tmp.tm_id')
        ->join('team_users AS tu','tu.tmu_id','=','tup.tmu_id','inner')
        ->join('users AS u','u.id','=','tu.u_id','inner')
        ->whereNull('p.deleted_at')
        ->whereNull('t.deleted_at')
        ->whereNull('u.deleted_at')
        ->whereNull('pr.deleted_at')
        ->whereNull('tu.deleted_at')
        ->whereNull('tmp.deleted_at')
        ->whereNull('tup.deleted_at');
    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('team')->setLabel("Team");
        $columnController->text('team')->setLabel("Team");
        $columnController->text('user_code')->setLabel('MR Code');
        $columnController->text('user_name')->setLabel('MR Name');
        $columnController->text('principal_code')->setLabel('Principal Code');
        $columnController->text('principal_name')->setLabel('Principal Name');
        $columnController->text('product_code')->setLabel('Product Code');
        $columnController->text('product_name')->setLabel('Product Name');
        $columnController->text("date")->setLabel("Allocated Date");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel("User")->setLink('user')->setWhere(['tm_id'=>'{team}','u_tp_id'=>config('shl.field_manager_type').'|'.config('shl.medical_rep_type').'|'.config('shl.product_specialist_type')])->setValidations('');
        $inputController->ajax_dropdown('principal')->setLabel("Principal")->setLink('principal')->setValidations('');
        $inputController->ajax_dropdown('product')->setLabel("Product")->setLink('product')->setWhere(['principal_id'=>'{principal}'])->setValidations('');
        $inputController->setStructure([['team','user'],['principal','product']]);
    }
}