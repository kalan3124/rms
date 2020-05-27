<?php
namespace App\Http\Controllers\Web\Reports;

use App\Exceptions\WebAPIException;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogReportController extends ReportController {
    protected $title = "Activity Log";

    protected $defaultSortColumn="date";

    protected $models = [
        1=>"Area",
        2=>"Bata Category",
        3=>"Bata Type",
        4=>"Brand",
        5=>"Chemist",
        6=>"Chemist Class",
        7=>"Chemist Market Description",
        8=>"Chemist Types",
        9=>"Day Type",
        10=>"District",
        11=>"Division",
        12=>"Doctor",
        13=>"Doctor Class",
        14=>"Doctor Speciality",
        15=>"Hospital Staff Category",
        16=>"Institution",
        17=>"Institution Category",
        18=>"Issue",
        19=>"Other Hospital Staff",
        20=>"Permission Group",
        21=>"Principal",
        22=>"Product",
        23=>"Product Family",
        24=>"Promotion",
        25=>"Province",
        26=>"Reason",
        27=>"Reason Type",
        28=>"Region",
        29=>"Route",
        30=>"Special Day",
        31=>"Sub Town",
        32=>"Team",
        33=>"Town",
        34=>"User",
        35=>"User Type",
        36=>"Vehicle Type",
        37=>"Vehicle Type Rate",
        38=>"Visit Type",
    ];

    public function search(Request $request){
        $sortBy = $request->input('sortBy');
        $userId = $request->input('values.user.value');
        $type = $request->input('values.type.value');
        $code = $request->input('values.code');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');
        $id = null;

        $query = Revision::query();

        if(isset($type)){
            $modelName = $this->models[$type];

            $modelName = str_replace(' ','',$modelName);
            $modelNameSql = "App\\\\Models\\\\$modelName";
            $modelName = "App\Models\\$modelName";

            $query->where('revisionable_type','LIKE',$modelNameSql);

            $modelName = "\\$modelName";

            if(isset($code)){
                $model = new $modelName();

                $instance = $model::where($model->getCodeName(),$code)->first();

                if($instance)
                    $id = $instance->getKey();
                else
                    throw new WebAPIException("Invalid code supplied!");

                $query->where('revisionable_id',$id);
            }
        } else if (isset($code)){
            throw new WebAPIException("Type field is required when searching by a code.");
        } else {
            $query->where(function($query){
                $modelNames = array_values($this->models);

                foreach ($modelNames as $key => $modelName) {
                    $modelName = str_replace(' ','',$modelName);
                    $modelNameSql = "App\\\\Models\\\\$modelName";

                    $query->orWhere(function($query) use($modelName,$modelNameSql){
                        $query->where('revisionable_type',"LIKE",$modelNameSql);

                        $formModelName = "App\Form\\$modelName";
                        $formModel = new $formModelName();
                        $names = [];

                        foreach ($formModel->getColumnController()->getColumns() as $key => $column) {
                            $names[] = $column->getName();
                        }

                        $names[] = 'deleted_at';

                        $query->whereIn('key',$names);

                    });

                }
            });
        }

        if(isset($userId)){
            $query->where('user_id',$userId);
        }
        
        if(isset($startDate)&&isset($endDate)){
            $query->whereDate('created_at','>=',$startDate);
            $query->whereDate('created_at','<=',$endDate);
        }
        

        switch ($sortBy) {
            case 'type':
                $sortBy = 'revisionable_type';
                break;
            case 'old_value':
                $sortBy = 'old_value';
                break;
            case 'new_value':
                $sortBy = 'new_value';
                break;
            default:
                $sortBy = 'created_at';
                break;
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $query->with('user');

        $results = $query->get();

        $results->transform(function($revision){
            $type = $revision->revisionable_type;

            $typeName = trim(preg_replace('/([A-Z])/',' $1',substr($type,11)));

            $formModelName = str_replace("Models","Form",$type);
            $formModel = new $formModelName();

            $query = $type::query();

            $formModel->beforeSearch($query,[]);

            $instance = $query->withTrashed()->find($revision->revisionable_id);
            $oldInstance = $query->select(['*',DB::raw('"'. addslashes($revision->old_value).'" AS '.$revision->key)])->find($revision->revisionable_id);

            $code = $instance->getCode();

            if($revision->key!='deleted_at'){
                try{
                    $column = $formModel->getColumnController()->getColumn($revision->key);
                } catch (\Exception $e){
                    return null;
                }

                $attribute = $column?$column->getLabel():$revision->key;

                $instance->{$revision->key} = $revision->new_value;

            } else {
                $attribute = "Deleted at";
            }

            return [
                'user'=>$revision->user?[
                    "value"=>$revision->user->id,
                    'label'=>$revision->user->name.' - '.$revision->user->u_code
                ]:[
                    'value'=>0,
                    "label"=>"Unknown"
                ],
                'type'=>$typeName,
                'code'=>$code,
                'attribute'=>$attribute,
                'old_value'=> $revision->key!='deleted_at'? $column->render($column->formatValue($revision->key,$oldInstance)):null,
                'new_value'=> $revision->key!='deleted_at'? $column->render($column->formatValue($revision->key,$instance)):$revision->new_value,
                'date'=>$revision->created_at->format("Y-m-d H:i:s"),    
            ];
        });

        $results = $results->filter(function($result){
            return !!$result;
        })->values();

        return [
            'count'=>$count,
            'results'=>$results
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->ajax_dropdown('user')->setLabel("User")->setSearchable(false);
        $columnController->text('type')->setLabel("Type");
        $columnController->text('code')->setLabel("Code")->setSearchable(false);
        $columnController->text('attribute')->setLabel("Field")->setSearchable(false);
        $columnController->text('old_value')->setLabel("Old Value");
        $columnController->text('new_value')->setLabel("New Value");
        $columnController->text('date')->setLabel("Changed Date");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user');
        $inputController->select('type')->setLabel("Type")->setOptions($this->models);
        $inputController->text('code')->setLabel("Code")->setValidations('');
        $inputController->date('s_date')->setLabel("From");
        $inputController->date('e_date')->setLabel("To");
        
        $inputController->setStructure([
            ['user','type','code'],
            ['s_date','e_date']
        ]);
    }
}