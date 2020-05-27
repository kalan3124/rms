<?php
namespace App\Ext\Get\HasModel;

use App\Ext\Get\Get;

class Model extends Get {
    /**
     * Our original model name
     *
     * @var string
     */
    public $originalModelName;
    /**
     * Code column name
     *
     * @var string
     */
    public $codeName;
    /**
     * Rows has model
     * 
     *  keys as foreign key names
     * 
     * @var RowsHasModel[]
     */
    public $subModels = [];
    /**
     * Column mapping
     * 
     * Keys as their table column names and values
     * as our table column names. Note:- Do not put
     * foreign keys
     *
     * @var array
     */
    public $columnMapping = [];

    /**
     * Updating our model if exists
     *
     * @param \App\Models\Base $inst
     * @param array $data
     * @return void
     */
    protected function createOrUpdate($data,$inst=null){
        $newData = [];

        foreach($this->columnMapping as $theirColumnName =>$ourColumnName){
            $newData[$ourColumnName] = $data[$theirColumnName];
        }

        foreach($this->subModels as $columnName=> $rowModel){
            $newData[$columnName] = $rowModel->execute($data);
        }

        $model = new $this->originalModelName();
            
        $newData[$model->getCodeName()] = $data[$this->codeName];

        if($inst)
            $inst->update($newData);
        else{

            $model::create($newData);
        }
    }

    public function afterUpdate($inst,$data){
        $model = new $this->originalModelName();

        $exists = $model::where($model->getCodeName(),"LIKE",$data[$this->codeName])->first();

        $this->createOrUpdate($data,$exists);
    }

    public function afterCreate($inst,$data){
        $this->afterUpdate($inst,$data);
    }
}