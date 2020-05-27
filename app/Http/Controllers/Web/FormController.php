<?php
namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Exports\ExpensesStatementSummeryReport;
use Illuminate\Http\Request;
use Validator;
use League\Csv\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use PDF;
use Excel;
use App\Http\Controllers\Controller;


class FormController extends Controller
{
    /**
     * Form data storing model
     *
     * @var \App\Form\Form
     */
    protected $formModel;
    /**
     * Basic model
     *
     * @var \App\Models\Base
     */
    protected $model;
    /**
     * Returning the form data storing data
     *
     * @param string $className form class name without namespace
     * @return App\Form\Form
     */
    protected function getFormModel($className)
    {
        $fullClassName = '\App\Form\\' . $className;

        if (class_exists($fullClassName)) {
            return new $fullClassName();
        } else {
            abort(404);
        }
    }
    /**
     * Setting the form model
     *
     * @param string $className
     * @return void
     */
    protected function setFormModel($className)
    {
        $this->formModel = $this->getFormModel($className);
    }
    /**
     * Returning the basic data model
     *
     * @param string $className
     * @return App\Models\Base
     */
    protected function getModel($className)
    {
        $fullClassName = '\App\Models\\' . $className;

        if (class_exists($fullClassName)) {
            return new $fullClassName();
        } else {
            abort(404);
        }
    }
    /**
     * Setting basic table model
     *
     * @param string $className
     * @return void
     */
    protected function setModel($className)
    {
        $this->model = $this->getModel($className);
    }
    /**
     * Returning the informations for the given form
     *
     * @param Request $request
     * @param string $link
     */
    public function getInformations(Request $request, string $link)
    {
        // converting link to classname
        $className = ucfirst(camel_case($link));
        // Setting the form model
        $this->setFormModel($className);

        $privilegedActions = $this->formModel->getPrivilegedActions();

        $inputs = $this->formModel->getInputController()->getOnlyPrivilegedInputs();

        $columns = $this->formModel->getColumnController()->getColumns();

        return response()->json([
            'privilegedActions' => $privilegedActions,
            'inputs' => $inputs,
            'structure' => $this->formModel->getInputController()->getStructure(),
            'title' => $this->formModel->getTitle(),
            'columns' => $columns,
            'inline' => $this->formModel->isInline(),
        ]);
    }

    public function dropdownSearch(Request $request, string $link)
    {

        $className = ucfirst(camel_case($link));
        $this->setFormModel($className);
        $this->setModel($className);

        $keyword = $request->input('keyword', '');
        $where = $request->input('where', []);
        $limit = $request->input('limit', true);

        $query = $this->model->query();

        $defaultValues = $this->formModel->getInputController()->getDefaultValues();

        $columns = $this->formModel->getColumnController()->getColumns();

        foreach ($defaultValues as $name => $value) {
            if (!isset($columns[$name])&&isset($value)) {
                $query->where($name, $value);
            }
        }
        $this->formModel->filterDropdownSearch($query,$where);

        if ($limit) {
            $query->limit(30);
        }

        $query->where(function ($query) use ($columns, $keyword) {
            foreach ($columns as $name => $column) {
                if ($column->isSearchable()&&$column->isDropdownSearchable()) {
                    $query->orWhere(...$column->getSearchCondition($keyword));
                }
            }
        });

        $this->formModel->beforeDropdownSearch($query, $keyword);
        $dropdownDisplay = $this->formModel->getDropdownDesplayPattern();

        $orderBy = explode(' - ', $dropdownDisplay);

        foreach ($orderBy as $key => $column) {
            $exploded = explode('.',$column);

            if(count($exploded)<2)
                $query->orderBy($column);
        }

        $results = $query->get();

        $results->transform(function ($inst) use ($dropdownDisplay) {
            return [
                'value' => $inst->getKey(),
                'label' => replaceStringWithAssocArray($inst->toArray(), $dropdownDisplay),
            ];
        });

        return $results->all();
    }

    protected function searchBy($link,$values, $sort, $sortMode, $deleted, $page, $perPage,$paginate=true)
    {
        $className = ucfirst(camel_case($link));
        $this->setFormModel($className);
        $this->setModel($className);

        $validator = Validator::make([
            'page'=>$page,
            'perPage'=>$perPage,
            'deleted'=>$deleted,
            'sortMode'=>$sortMode
        ], [
            'page' => 'numeric',
            'perPage' => 'numeric',
            'deleted' => 'boolean',
            'sortMode' => 'in:asc,desc',
        ]);

        if ($validator->fails()) {
            throw new WebAPIException($validator->errors()->first(), 1);
        }

        $query = $this->model->query();

        $defaultValues = $this->formModel->getInputController()->getDefaultValues();
        $columns = $this->formModel->getColumnController()->getColumns();

        if (!in_array($sort, array_keys($columns))) {
            throw new WebAPIException("Provided sorting column is invalid", 2);
        }

        $values = array_merge($defaultValues, $values);

        foreach ($columns as $name => $column) {
            if ($column->isSearchable() && !empty($values[$name])) {
                $query->where(...$column->getSearchCondition($column->fetchValue($values[$name])));
            }
        }

        if ($deleted) {
            $query->onlyTrashed();
        }

        $query->orderBy($sort, $sortMode);

        $this->formModel->beforeSearch($query, $values);

        $totalCount = $query->count();

        if($paginate){
            $query->take($perPage);

            $query->skip(($page - 1) * $perPage);
        }


        $results = $query->get();

        $results->transform(function ($item) {
            $itemArr = $this->formModel->formatResult($item);

            if(!isset($itemArr['deleted']))
                $itemArr['deleted'] = false;

            $itemArr['id'] = $item->getKey();

            return $itemArr;
        });

        return [
            'count' => $totalCount,
            'results' => $results->all(),
        ];
    }

    protected function getSearchParametersByRequest(Request $request){
        return [
            $request->input('values', []),
            $request->input('sortBy', 'created_at'),
            $request->input('sortMode', 'desc'),
            $request->input('deleted', false),
            $request->input('page', 1),
            $request->input('perPage', 25)
        ];
    }

    public function search(Request $request, string $link)
    {
        return response()->json($this->searchBy($link,...$this->getSearchParametersByRequest($request)));
    }

    public function submit(Request $request, string $link, string $mode)
    {
        $className = ucfirst(camel_case($link));
        $this->setFormModel($className);
        $this->setModel($className);

        $inputs = $this->formModel->getInputController()->getOnlyPrivilegedInputs();
        $defaultValues = $this->formModel->getInputController()->getDefaultValues();

        $values = $request->input('values', []);
        $this->formModel->validate($values);

        $validatedValues = [];

        foreach ($inputs as $name => $input) {
            if (isset($values[$name])) {
                $validatedValues[$name] = $input->fetchValue($values[$name], $values);
            }
        }

        $validatedValues = array_merge($defaultValues, $validatedValues);

        if ($mode == 'create') {
            $formatedValues = $this->formModel->beforeCreate($validatedValues);
            $exists = $this->model->create($formatedValues);
        } else {
            $id = $request->input('id', null);
            $exists = $this->model->find($id);
            if (!$exists) {
                throw new WebAPIException("Selected item not exists!", 3);
            }

            $formatedValues = $this->formModel->beforeUpdate($validatedValues, $exists);
            $exists->update($formatedValues);
        }

        $this->formModel->{'after' . $mode}($exists, $validatedValues);

        return response()->json([
            "success" => true,
            "message" => "Successfully $mode" . "ed your " . $this->formModel->getTitle() . "!",
        ]);
    }

    public function delete(Request $request, string $link)
    {
        $className = ucfirst(camel_case($link));
        $this->setFormModel($className);
        $this->setModel($className);

        $exists = $this->model->find($request->input('id', 0));

        if (!$exists) {
            throw new WebAPIException("Selected " . $this->formModel->getTitle() . " is not exists!", 4);
        }

        $delete = $this->formModel->beforeDelete($exists);


        if($delete)
            $exists->delete();

        $this->formModel->afterDelete($request->input('id'));

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted the " . $this->formModel->getTitle(),
        ]);
    }

    public function restore(Request $request, string $link){
        $className = ucfirst(camel_case($link));
        $this->setFormModel($className);
        $this->setModel($className);

        $exists = $this->model->withTrashed()->find($request->input('id', 0));

        if (!$exists) {
            throw new WebAPIException("Selected " . $this->formModel->getTitle() . " is not exists!", 4);
        }

        $restore = $this->formModel->beforeRestore($exists);

        if($restore)
            $exists->restore();

        $this->formModel->afterRestore($request->input('id'));


        return response()->json([
            'success'=> true,
            'message'=> "Successfully restored your ". $this->formModel->getTitle()
        ]);
    }

    protected function rightAlignedRow($label,$value,$count){
        $emptyRow = array_fill(0,$count,'');

        $emptyRow[$count-3] = $label;
        $emptyRow[$count-2] = ':-';
        $emptyRow[$count-1] = $value;

        return $emptyRow;
    }

    public function saveCSV(Request $request, string $link)
    {

        $searchedParameters = $this->getSearchParametersByRequest($request);

        $searchedParameters[] = false;

        $results =  $this->searchBy($link,...$searchedParameters);

        $columns = $this->formModel->getColumnController()->getColumns();

        $data = [] ;

        $columnNames = array_map(function($column){
            return $column->getLabel();
        },array_values($columns));
        $columnCounts = count($columnNames);

        $emptyRow = array_fill(0,$columnCounts,'');

        $titleRow = $emptyRow;
        $titleRow[floor($columnCounts/2)-1] = $this->formModel->getTitle().'s';
        $data[] = $titleRow;


        $data[] = $emptyRow;

        $values = $searchedParameters[0];

        if(count($values)>0){

            $data[] = $this->rightAlignedRow("Searched Terms",'',$columnCounts);


            foreach($this->formModel->getColumnController()->getColumns() as $name=>$column){
                if(isset($values[$name])){
                    $data[] = $this->rightAlignedRow($column->getLabel(),$column->render($values[$name]),$columnCounts);
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
                $renderedRow[] = $column->render($result[$name]);
            }

            $data[] = $renderedRow;
        }

        $data[] = $emptyRow;
        $data[] = $emptyRow;

        // $data[] = $this->rightAlignedRow('Page',$searchedParameters[4],$columnCounts);
        // $data[] = $this->rightAlignedRow('Rows Per Page',$searchedParameters[5],$columnCounts);
        $data[] = $this->rightAlignedRow('Sorted By',($columns[$searchedParameters[1]])->getLabel(),$columnCounts);
        $data[] = $this->rightAlignedRow('Sorted Mode',$searchedParameters[2]=='desc'?'Desccending':"Ascending",$columnCounts);
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

    public function savePDF(Request $request, string $link)
    {
        $searchedParameters = $this->getSearchParametersByRequest($request);

        $results =  $this->searchBy($link,...$searchedParameters);

        $columns = $this->formModel->getColumnController()->getColumns();

        $data = [] ;

        $columnNames = array_map(function($column){
            return $column->getLabel();
        },array_values($columns));

        $data['columns'] = $columnNames;

        $data['title']= str_plural($this->formModel->getTitle());

        $data['searchTerms'] = [];

        $values = $searchedParameters[0];

        if(count($values)>0){


            foreach($this->formModel->getColumnController()->getColumns() as $name=>$column){
                if(isset($values[$name])){
                    $data['searchTerms'][] = ['label'=>$column->getLabel(),'value'=>$column->render($values[$name])];
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

        $data['page'] = $searchedParameters[4];
        $data['per_page'] = $searchedParameters[5];
        $data['sorted_by'] = ($columns[$searchedParameters[1]])->getLabel();
        $data['sorted_mode'] = $searchedParameters[2]=='desc'?'Desccending':"Ascending";
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

    public function saveXLSX(Request $request, string $link)
    {
        $searchedParameters = $this->getSearchParametersByRequest($request);

        $searchedParameters[] = false;

        $results =  $this->searchBy($link,...$searchedParameters);

        $columns = $this->formModel->getColumnController()->getColumns();

        $data = [] ;

        $data['columns'] =(array) $columns;

        $data['title']= str_plural($this->formModel->getTitle());

        $data['searchTerms'] = [];

        $values = $searchedParameters[0];

        if(count($values)>0){


            foreach($this->formModel->getColumnController()->getColumns() as $name=>$column){
                if(isset($values[$name])){
                    $data['searchTerms'][] = ['label'=>$column->getLabel(),'value'=>$column->render($values[$name])];
                }
            }
        }

        $data['results'] = $results['results'];
        $user = Auth::user();

        $data['sorted_by'] = ($columns[$searchedParameters[1]])->getLabel();
        $data['sorted_mode'] = $searchedParameters[2]=='desc'?'Desccending':"Ascending";
        $data['time'] = date('Y-m-d H:i:s');
        $data['user'] = $user;
        $userId = $user->getKey();

        $userCode = str_replace([' ','/'.'\\','.'],'_',$user->u_code) ;
        $userName = str_replace([' ','/'.'\\','.'],'_',$user->name);

        $time = time();

        // return view('excels.reports',$data);
        $data['link'] = url('/storage/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

        Excel::store(new ExpensesStatementSummeryReport($data),'public/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

        return response()->json([
            'file'=>$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx'
        ]);

    }


    public function test(Request $request){
        return 'ok';
    }
}
