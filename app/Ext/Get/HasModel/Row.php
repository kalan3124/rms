<?php
namespace App\Ext\Get\HasModel;

class Row {
    /**
     * Our Model Name
     *
     * @var string
     */
    public $modelName;
    /**
     * Code column name
     *
     * @var string
     */
    public $codeName;
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
     * Setting values in construct
     *
     * @param \App\Models\Base $modelName
     * @param string $codeName
     * @param array $columnMapping
     */
    public function __construct($modelName,$codeName,$columnMapping=[])
    {
        $this->modelName = $modelName;
        $this->codeName = $codeName;
        $this->columnMapping = $columnMapping;
    }
    /**
     * Creating or updating our model and
     * returning the primary key of this model
     *
     * @param array $data
     * @return int
     */
    public function execute($data){
        $newData = [];

        $updated = false;

        foreach($this->columnMapping as $ourColumnName=>$theirColumnName ){
            $value = null;
            if(is_object($theirColumnName)){
                $value = $theirColumnName->execute($data);
            } else {
                $value =$data[$theirColumnName];
            }
            if(isset($value)){
                $updated = true;
                $newData[$ourColumnName] = $value;
            }
        }

        $model = new $this->modelName();

        $exists = $model->where($model->getCodeName(),"LIKE",$data[$this->codeName])->first();

        if(empty($newData)){
            if($exists){
                return $exists->getKey();
            } else {
                return null;
            }
        }

        if($exists&&$updated){
            $exists->update($newData);
        } else {
            $newData[$model->getCodeName()] = $data[$this->codeName];
            $exists = $model::create($newData);
        }

        return $exists->getKey();
    }
}