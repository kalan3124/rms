<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use Illuminate\Http\JsonResponse;
use App\CSV\Base;
use Illuminate\Support\Facades\Storage;
use App\Jobs\UploadCSV;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class UploadCSVController extends Controller{
    /**
     * CSV Instance
     *
     * @var Base
     */
    protected $instance;
    /**
     * Returning the informations of a csv file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request){
        $this->setModel($request->all());

        return [
            'success'=>true,
            'title'=>$this->instance->getTitle(),
            'tips'=>$this->instance->getTips()
        ];
    }
    /**
     * Creating a new csv model
     *
     * @param string $name
     * @param boolean $isForm
     * @return App\CSV\Base
     */
    protected function setModel($data){
        $validation = Validator::make($data,[
            'name'=>'required',
            'type'=>'required|in:1,2'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request");
        }

        $name = $data['name'];
        $type = $data['type'];

        $isForm = $type==1;

        $className = ucfirst(camel_case($name));

        if($isForm){
            $classNamespace = "\App\Form\\".$className;

            $modelNamespace = '\App\Models\\'.$className;

            $formInstance = new $classNamespace();

            $modelInstance = new $modelNamespace();
            
            $this->instance =  new \App\CSV\Form($formInstance,$modelInstance);
        } else {
            $classNamespace = "\App\CSV\\".$className;

            $this->instance = new $classNamespace();
        }
    }
    /**
     * Download format for csv
     * 
     * @param Request $request
     * @param int $type
     * @param string $name
     */
    public function downloadFormat( Request $request){
        $this->setModel($request->all());

        $name = $request->input('name');

        $columns = $this->instance->getColumnLabels();

        Storage::put('/public/csv_formats/'.$name.'.csv', implode(',',$columns));

        return response()->json([
            "success"=>true
        ]);
    }

    public function uploadFile(Request $request){
        $validation = Validator::make($request->all(),[
            'fileName'=>'required'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Can not validate your request.");
        }

        $user = Auth::user();

        $fileName = $request->input('fileName');

        $exists = Storage::exists('public/csv/'.$fileName);

        if(!$exists) throw new WebAPIException("Uploaded file is deleted or moved.");

        $this->setModel($request->all());

        $this->instance->setFilePath('app/public/csv/'.$fileName);

        $this->instance->setUser($user);

        $this->instance->logStatus("starting","Please wait starting your process",0);

        $linesCount = $this->instance->getSize();
        
        UploadCSV::dispatch($this->instance);

        return response()->json([
            "success"=>true,
            "message"=>"Successfully uploaded your file. Please wait till insert",
            "lines"=>$linesCount
        ]);
    }
    /**
     * Checking the status of process
     * 
     */
    public function checkStatus(){
        $user = Auth::user();

        $jsonString = Storage::get('/csv_progress/'.$user->getKey().'.json');
        
        if(! $jsonString) throw new WebAPIException("Can not find a progress");

        $result = json_decode($jsonString,true);

        return response()->json($result);
    }
}