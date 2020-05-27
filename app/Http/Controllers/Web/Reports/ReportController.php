<?php

namespace App\Http\Controllers\Web\Reports;

use App\Exports\ExpensesStatementSummeryReport;
use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use App\Form\Inputs\InputController;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Updating columns on searching
     *
     * @var boolean
     */
    protected $updateColumnsOnSearch = false;
    /**
     * Report name
     *
     * @var string
     */
    protected $title = "";
    /**
     * Controlling table columns
     *
     * @var ColumnController
     */
    protected $columns;
    /**
     * Controlling form inputs
     *
     * @var InputController
     */
    protected $inputs;

    protected $defaultSortColumn;

    protected $additionalHeaders = [];

    protected $isCSV = false;

    protected function setColumns(ColumnController $columnController, Request $request){
    }

    protected function setInputs(InputController $inputController){
    }

    public function search(Request $request){
        return [
            'count'=>0,
            'results'=>[]
        ];
    }

    public function getResults(Request $request){
        $this->updateColumns($request);

        $returnArr = $this->search($request);
        if($this->updateColumnsOnSearch){
            $returnArr['columns'] = $this->columns->getColumns();
            $returnArr["additionalHeaders"] = $this->getAdditionalHeaders($request);
        }

        return $returnArr;
    }

    protected function updateColumns($request = null){

        $columns = new ColumnController();
        $this->setColumns($columns,$request);
        $this->columns = $columns;
    }

    protected function boot(){
        $inputs = new InputController();
        $this->setInputs($inputs);
        $this->inputs = $inputs;

        // $this->updateColumns();

    }

    protected function getAdditionalHeaders($request){
        return $this->additionalHeaders;
    }

    public function info(Request $request){
        $this->boot();
        $this->updateColumns($request);

        $inputs = $this->inputs->getOnlyPrivilegedInputs();

        $columns = $this->columns->getColumns();

        return [
            'updateColumnsOnSearch'=>$this->updateColumnsOnSearch,
            'title'=>$this->title,
            'inputsStructure'=>$this->inputs->getStructure(),
            'inputs'=>$inputs,
            'columns'=>$columns,
            'additionalHeaders'=>$this->getAdditionalHeaders($request)
        ];
    }

    protected function rightAlignedRow($label,$value,$count){
        $emptyRow = array_fill(0,$count,'');

        $emptyRow[$count-3] = $label;
        $emptyRow[$count-2] = ':-';
        $emptyRow[$count-1] = $value;

        return $emptyRow;
    }

    public function saveCSV(Request $request)
    {
        $this->boot();
        $this->updateColumns($request);

        $values = $request->input('values');

        $this->isCSV=true;

        $results =  $this->search($request);

        $columns = $this->columns->getColumns();

        $data = [] ;

        $columnNames = array_map(function($column){
            return $column->getLabel();
        },array_values($columns));
        $columnCounts = count($columnNames);

        $emptyRow = array_fill(0,$columnCounts,'');

        $titleRow = $emptyRow;
        $titleRow[floor($columnCounts/2)-1] = $this->title;
        $data[] = $titleRow;


        $data[] = $emptyRow;

        $values = $request->input('values');


        $inputs = $this->inputs->getOnlyPrivilegedInputs();

        if(count($values)>0){

            $data[] = $this->rightAlignedRow("Searched Terms",'',$columnCounts);


            foreach($inputs as $name=>$input){
                if(isset($values[$name])){
                    $data[] = $this->rightAlignedRow($input->getLabel(),$input->render($values[$name]),$columnCounts);
                }
            }

            $data[] = $emptyRow;
        }
        $data[] = $emptyRow;
        $data[] = $emptyRow;

        $data[] = $columnNames;

        foreach($results['results'] as $result){
            $renderedRow = [];

            foreach($columns as $name=>$column){
                if(isset($result[$name]))
                    $renderedRow[] = $column->render($result[$name]);
            }

            $data[] = $renderedRow;
        }

        $data[] = $emptyRow;
        $data[] = $emptyRow;
        

        // $data[] = $this->rightAlignedRow('Page',$request->input('page'),$columnCounts);
        // $data[] = $this->rightAlignedRow('Rows Per Page',$request->input('perPage'),$columnCounts);

        if($request->has(['sortBy','sortMode'])){
            $data[] = $this->rightAlignedRow('Sorted By',($columns[$request->input('sortBy')])->getLabel(),$columnCounts);
            $data[] = $this->rightAlignedRow('Sorted Mode',$request->input('sortMode')=='desc'?'Desccending':"Ascending",$columnCounts);
        }

        $data[] = $this->rightAlignedRow('Generated At',date('Y-m-d H:i:s'),$columnCounts);

        $companyRow = $emptyRow;
        $companyRow[0] = date('Y');
        $companyRow[1] = 'Ceylon Linux (PVT) LTD ';
        $companyRow[2] = 'All Rights Reserved.';
        $data[] = $emptyRow;
        $data[] = $emptyRow;
        $data[] = $companyRow;

        $csv = Writer::createFromString('');
        $csv->insertAll($data);

        $userId = Auth::user()->getKey();
        $time = time();

        Storage::put('public/csv/'.$userId.'/'.$time.'.csv',$csv->getContent()) ;

        return response()->json([
            'file'=>$userId.'/'.$time.'.csv'
        ]);
    }

    public function savePDF(Request $request)
    {
        $this->boot();
        $this->updateColumns($request);

        $values = $request->input('values');

        $results =  $this->search($request);

        $columns = $this->columns->getColumns();

        $data = [] ;

        $data = [] ;

        $columnNames = array_map(function($column){
            return $column->getLabel();
        },array_values($columns));

        $data['columns'] = $columnNames;

        $data['title']= $this->title;

        $data['searchTerms'] = [];

        $inputs = $this->inputs->getOnlyPrivilegedInputs();

        if(count($values)>0){
            foreach($inputs as $name=>$input){
                if(isset($values[$name])){
                    $data['searchTerms'][] = ['label'=>$input->getLabel(),'value'=>$input->render($values[$name])];
                }
            }
        }

        $data['results'] = [];

        foreach($results['results'] as $result){
            $renderedRow = [];

            foreach($columns as $name=>$column){
                $renderedRow[] = $column->render($result[$name]);
            }

            $data['results'][] = $renderedRow;
        }

        $data['page'] = $request->input('page');
        $data['per_page'] = $request->input('perPage');
        $data['sorted_by'] = $request->input('sortBy');
        $data['sorted_mode'] = $request->input('sortMode')=='desc'?'Desccending':"Ascending";
        $data['time'] = date('Y-m-d H:i:s');

        $userId = Auth::user()->getKey();
        $time = time();

        $pdf = PDF::loadView('pdf', $data);

        $content = $pdf->download()->getOriginalContent();

        Storage::put('public/pdf/'.$userId.'/'.$time.'.pdf',$content) ;

        return response()->json([
            'file'=>$userId.'/'.$time.'.pdf'
        ]);
    }

    public function saveXlsx(Request $request)
    {
        $this->boot();
        $this->updateColumns($request);

        $values = $request->input('values');

        $this->isCSV=true;

        $results =  $this->search($request);

        $columns = $this->columns->getColumns();

        $data = [] ;

        $columnNames = array_map(function($column){
            return $column->getLabel();
        },array_values($columns));

        $data['columns'] =(array) $this->columns->getColumns();
        $data["additionalHeaders"] =(array) $this->getAdditionalHeaders($request);

        $data['title']= $this->title;

        $data['searchTerms'] = [];

        $inputs = $this->inputs->getOnlyPrivilegedInputs();

        if(count($values)>0){
            foreach($inputs as $name=>$input){
                if(isset($values[$name])){
                    $data['searchTerms'][] = ['label'=>$input->getLabel(),'value'=>$input->render($values[$name])];
                }
            }
        }

        $data['results'] = $results['results'];
        $user = Auth::user();

        $data['sorted_by'] = $request->input('sortBy');
        $data['sorted_mode'] = $request->input('sortMode')=='desc'?'Desccending':"Ascending";
        $data['time'] = date('Y-m-d H:i:s');
        $data['user'] = $user;
        $userId = $user->getKey();

        if($request->input('values.user.value')){
            $user = User::find($request->input('values.user.value'));
        }

        if($request->input('values.u_id.value')){
            $user = User::find($request->input('values.u_id.value'));
        }

        if($request->input('values.u_name.value')){
            $user = User::find($request->input('values.u_name.value'));
        }

        $userCode = str_replace([' ','/'.'\\','.'],'_',$user->u_code) ;
        $userName = str_replace([' ','/'.'\\','.'],'_',$user->name);

        $time = time();

        $data['link'] = url('/storage/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

        // return view('excels.reports',$data);

        Excel::store(new ExpensesStatementSummeryReport($data),'public/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

        return response()->json([
            'file'=>$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx'
        ]);
        
    }

    protected function paginateAndCount($query,$request,$orderBy=null){
        $page = $request->input('page')??1;
        $perPage = $request->input('perPage')??25;
        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = $orderBy??($request->input('sortBy')??$this->defaultSortColumn);


        $query->orderBy($sortBy,$sortMode);


        $count = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings(get_class($query)=='Illuminate\Database\Eloquent\Builder'?$query->getQuery():$query)->count();

        if(!$this->isCSV){
            $query->take($perPage);

            $query->skip(($page-1)*$perPage);
        }

        return $count;
    }
    
    public function datePeriod($fromDate,$toDate){
            $begin = new \DateTime(date('Y-01-01',strtotime($fromDate)));
            $end = new \DateTime(date('Y-m-d',strtotime($toDate)));
            
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($begin, $interval, $end);

            return $period;
    }
}