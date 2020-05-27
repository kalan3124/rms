<?php

namespace App\CSV;

use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * This is the base class for all css uploads
 */
class Base {
    /**
     * Previous row of current process
     *
     * @var array
     */
    protected $previousRow = [];
    /**
     * Row number previously processed
     *
     * @var integer
     */
    protected $previousRowNumber = 0;
    /**
     * Title of the given CSV
     *
     * @var string
     */
    protected $title = "";
    /**
     * Csv file path
     *
     * @var string
     */
    protected $filePath;
    /**
     * CSV File resource returned from fopen
     *
     * @var resource
     */
    protected $handler;
    /**
     * Data storing in this array after validation
     *
     * @var array
     */
    protected $data=[];
    /**
     * Setting the logged user
     *
     * @var \App\Models\User
     */
    protected $loggedUser;
    /**
     * Column labels and names
     * 
     * Name as key and label as value
     *
     * @var array
     */
    protected $columns = [];
    /**
     * Tips to display in frontend
     *
     * @var array
     */
    protected $tips = [

    ];
    /**
     * Common tips for display in all csv upload pages
     *
     * @var string[]
     */
    protected $commonTips = [
        "Check twise your codes with reference codes.",
        "Use 'PREV' keyword if you want to copy the value in above cell.",
        "Put '@' mark at the begining of the codes if it starts with zeros(0)",
        "Do not use commas(,) when inserting amounts and prices."
    ];
    /**
     * Returning the title of the csv file
     *
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }
    /**
     * Returning column names for the csv
     * 
     * @return array
     */
    public function getColumnNames(){
        return array_keys($this->columns);
    }
    /**
     * Returning all column labels
     * 
     * @return string[]
     */
    public function getColumnLabels(){
        return array_values($this->columns);
    }
    /**
     * Setting file path for the csv file
     *
     * @param string $filePath
     * @return void
     */
    public function setFilePath($filePath){
        $this->filePath =  storage_path($filePath);
    }
    /**
     * Opening a file
     * 
     * @param string $filePath
     */
    public function openFile(){
        $this->handler = fopen($this->filePath,'r');
    }
    /**
     * Get file size from csv
     * 
     * @return int
     */
    public function getSize(){
        return count(file($this->filePath))-1;
    }
    /**
     * Validating results
     *
     * @return void
     */
    public function format(){
        $count = 0;

        fgetcsv($this->handler);

        while(($row=fgetcsv($this->handler))!==FALSE){
            $count++;

            try{
                $formatedRow = $this->formatRow($row);

                $this->validateRow($formatedRow);

                $this->data[] = $formatedRow;
                $this->previousRowNumber = $count;
                $this->logStatus("formating","Please wait formating your csv",$count);

            } catch(WebAPIException $e){
                $message = $e->getMessage()." in line ".($count+1);

                $this->logStatus("error",$message,$count-1);

                Storage::put('/public/errors/'.date("Y/m/d")."/".$this->getUser()->getKey().".txt",date("H:i:s")."\n----\n".$e->__toString()."\n\n".json_encode($this->previousRow));
                
                if(is_resource($this->handler))
                    fclose($this->handler);

                return false;
            }

        }

        return true;

    }
    /**
     * Formating a row
     *
     * @param array $row
     * @return array
     */
    public function formatRow($row){
        $columnNames = $this->getColumnNames();

        $formatedArr = [];
        $displayArr = [];


        foreach ($columnNames as $key => $name) {
            $value = trim($row[$key]);
            
            // Leading zeros
            if(!empty($value))
                $value = preg_replace('/(\@)(.*)/',"$2",$value);

            if(empty($value)&&$value!=0){
                $value = null;
            } elseif(strtolower($value)=="prev"){
                $value = $this->previousRow[$name];
            } else if(strtolower($value)=="null"){
                $value = null;
            }
            
            $displayArr[$name] = $value;
            $formatedArr[$name] = $this->formatValue($name,$value);
        }
        
        $this->previousRow = $displayArr;

        return $formatedArr;
    }
    /**
     * Formating a value for a column
     *
     * @param string $columnName
     * @param string $value
     * @return void
     */
    protected function formatValue($columnName,$value){
        return $value;
    }
    /**
     * Validating a row
     *
     * @param array $formatedRow
     * @throws WebAPIException
     */
    protected function validateRow($formatedRow){
        
    }
    /**
     * Trigger an action before insert process start
     *
     * @return void
     */
    protected function beforeInsert(){

    }
    /**
     * Inserting to the database
     * 
     */
    public function insert(){

        $line = 0;
        try{
            DB::beginTransaction();

            $this->beforeInsert();

            foreach($this->data as $key=> $row){
                $line++;

                $this->insertRow($row);

                $this->logStatus('inserting',"Inserting your csv to database.",$line);

                $this->previousRow = $row;
                $this->previousRowNumber = $line;
            }

            DB::commit();

        } catch (WebAPIException $e){

            $message = $e->getMessage()." in line ".($line+1)." .";

            Storage::put('/public/errors/'.date("Y/m/d")."/".$this->getUser()->getKey().".txt",date("H:i:s")."\n----\n".$e->__toString()."\n\n".json_encode($this->previousRow));

            $this->logStatus('error',$message,$line);

            DB::rollback();

            return false;

        }

        return true;
    }
    /**
     * Inserting row to database
     *
     * @param array $row
     * @return void
     */
    protected function insertRow($row){

    }
    /**
     * Setting the logged user instance. It is using for unique directory creating
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function setUser($user){
        $this->loggedUser = $user;
    }
    /**
     * Returning the logged user
     *
     * @return \App\Models\User
     */
    public function getUser(){
        return $this->loggedUser;
    }
    /**
     * Logging the status
     * 
     * @param string $status error|success|formating|inserting
     * @param string $message
     * @param int $currentLine
     * @return bool
     */
    public function logStatus($status,$message,$currentLine=0){

        $user = $this->loggedUser;

        $content = json_encode([
            'status'=>$status,
            'message'=>$message,
            'currentLine'=>$currentLine
        ]);

        $name = '/csv_progress/'.$user->getKey().'.json';

        Storage::put($name,$content);

        return true;
    }
    /**
     * Closing the file
     *
     * @return void
     */
    protected function closeFile(){
        fclose($this->handler);
    }
    /**
     * Logging the success status
     *
     * @return void
     */
    public function success(){
        $this->logStatus('success',"Successfully insert your csv file!",$this->previousRowNumber) ;
    }
    /**
     * Returning the tips
     * 
     * @return string[]
     */
    public function getTips(){
        return array_merge($this->tips,$this->commonTips);
    }
}