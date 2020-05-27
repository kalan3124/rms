<?php

namespace App\Ext\Get;

use Illuminate\Database\Eloquent\Model;

class Get extends Model
{
    protected $connection = 'oracle';
    /**
     * Is this model has primary keys
     * 
     * When you syncronizing the data from server
     * it will checking this attribute. The table
     * will updating if this true
     *
     * @var boolean
     */
    public $hasPrimary = true;
    /**
     * Is model using composite primary keys
     * 
     * pass an array to $primaryKey attribute
     * if this turned on
     *
     * @var boolean
     */
    public $hasCompositePrimary = false;
    /**
     * Setting increment turn off. Other wise
     * primary key is returning as a integer value
     *
     * @var boolean
     */
    public $incrementing = false;

    public function __construct()
    {
        parent::__construct();

        $this->setTable($this->table);
        $this->setConnection($this->connection);
    }
    /**
     * Trigger an action after created
     *
     * @param \App\Models\Base $inst
     * @param array $data
     * @return void
     */
    public function afterUpdate($inst,$data){
       
    }
    /**
     * Trigger an action after create
     *
     * @param \App\Models\Base $inst
     * @param array $data
     * @return void
     */
    public function afterCreate($inst,$data){

    }
}

