<?php

namespace App\Form;

use App\Form\Inputs\InputController;
use App\Form\Columns\ColumnController;
use App\Form\HasPrivileges;
use App\Form\Actions\Create;
use App\Form\Actions\Update;
use App\Form\Actions\Delete;
use App\Form\Actions\Action;
use App\Models\Base;
use App\Models\PermissionGroupUser;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Form extends HasPrivileges {
    /**
     * Form inputs storing
     *
     * @var InputController
     */
    protected $inputs;
    /**
     * Table columns storing
     *
     * @var ColumnController
     */
    protected $columns;
    /**
     * Actions
     *
     * @var Action[]
     */
    protected $actions=[];
    /**
     * Form title
     *
     * @var string
     */
    protected $title = 'Sample Title';
    /**
     * Desplaying pattern for dropdowns
     *
     * @var string
     */
    protected $dropdownDesplayPattern = '';
    /**
     * Form and table inline
     *
     * @var string
     */
    protected $inline = true;
    /**
     * Validation rules
     *
     * @var array
     */
    protected $validations = [];

    public function __construct(){

        $this->inputs = new InputController();
        $this->setInputs($this->inputs);

        $this->columns =  new ColumnController();
        $this->setColumns($this->columns);

        $className = substr(get_class($this),9);
        $className = preg_replace_callback('/([A-Z])/',function($match){
            if(isset($match[0]))
                return '_'.strtolower($match[0]);
        }, lcfirst($className));

        $user = Auth::user();

        $permissionGroups = PermissionGroupUser::with('permissionGroup')
            ->where(function($query) use($user) {
                $query->orWhere('u_id',$user->getKey());
                $query->orWhere('u_tp_id',$user->u_tp_id);
            })
            ->get();

        $permissions = UserPermission::with('permission')->where('u_id',$user->getKey())->get();

        if(!$permissions->count()){
            $permissions = UserPermission::with('permission')->whereIn('pg_id',$permissionGroups->pluck('pg_id'))->get();
        }

        $permissions->transform(function(UserPermission $userPermission){
            if(!isset($userPermission->permission))
                return null;

            return $userPermission->permission->perm_code;
        });


        $subJson = Storage::get('/menu/sub.json');
        $subMenu = json_decode($subJson,true);
        $actions = [];

        foreach ($permissions as $key => $permission) {
            if($permission){
                $exploded = explode('.',$permission);

                if(count($exploded)==2){
                    $menu = $subMenu[$exploded[0]][$exploded[1]];

                    $links = array_map(function($item){
                        $explodedLink = explode('/',$item['link']);

                        if(count($explodedLink)<2)
                            return null;

                        if($explodedLink[0]=='panel')
                            return null;

                        return $explodedLink[1];
                    },$menu['items']);

                    if(in_array($className,array_values($links))){
                        $actions = array_merge($actions,['create','update','delete']);
                    }
                } else if(count($exploded)==3){
                    $link = $subMenu[$exploded[0]][$exploded[1]]['items'][$exploded[2]]['link'];

                    $link = substr($link,6);

                    if($link==$className){
                        $actions = array_merge($actions,['create','update','delete']);
                    }
                } else if(count($exploded)==4){
                    $link = $subMenu[$exploded[0]][$exploded[1]]['items'][$exploded[2]]['link'];

                    $link = substr($link,6);

                    if($link==$className){
                        if($exploded[3]=='view')
                            $actions[] = 'view';
                        else
                            $actions[] = $exploded[3];
                    }
                }
            }
        }

        if($user->u_tp_id==1){
            $actions = ['create','update','delete'];
        }

        foreach ($actions as $key => $action) {
            if($action!='view'){
                $actionName = '\App\Form\Actions\\'.ucfirst($action);
                $this->actions[$action] = new $actionName();
            }
        }

        $this->boot();
    }
    /**
     * Setting inputs to form
     *
     * @param InputController $inputController
     * @return void
     */
    protected function setInputs(InputController $inputController){
    }
    /**
     * Setting table columns
     *
     * @param ColumnController $columnController
     * @return void
     */
    protected function setColumns(ColumnController $columnController){
        foreach($this->inputs->getOnlyPrivilegedInputs() as $name=>$input){
            if($input->getType()!='password')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
        }
        $columnController->date('created_at')->setLabel("Created Date");
    }
    /**
     * Returning the pattern to desplay on dropdowns
     *
     * @return string
     */
    public function getDropdownDesplayPattern(){
        return $this->dropdownDesplayPattern;
    }
    /**
     * Executing when form loading
     *
     * You can do anything in this action.
     *
     * @return void
     */
    protected function boot(){
    }
    /**
     * Returning the input controller
     *
     * @return InputController
     */
    public function getInputController(){
        return $this->inputs;
    }
    /**
     * Returning an action for the form
     *
     * @param string $name
     * @return Action
     */
    protected function getAction($name){
        return $this->actions[$name];
    }
    /**
     * Returning only privileged actions
     *
     * @return Action[]
     */
    public function getPrivilegedActions(){
        $privilegedActions = [];

        foreach ($this->actions as $name =>$action) {
            if($action->isPrivileged()) $privilegedActions[] = $name;
        }

        return $privilegedActions;
    }

    public function hasPrivilege($actionName){
        if(isset($this->actions[$actionName])) return $this->actions[$actionName]->isPrivileged();

        return false;
    }
    /**
     * Returning the title of the form
     *
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }
    /**
     * Returning the column controller for get columns
     *
     *
     * @return ColumnController
     */
    public function getColumnController(){
        return $this->columns;
    }
    /**
     * Checking the weather the form component and table is inline
     *
     * @return boolean
     */
    public function isInline(){
        return !!$this->inline;
    }
    /**
     * Runing function before dropdown items searching
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $keyword
     */
    public function beforeDropdownSearch($query,$keyword){

    }
    /**
     * Filtering search results in dropdown
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $where
     * @return void
     */
    public function filterDropdownSearch($query,$where){
        foreach ($where as $name => $values) {

            if(!is_array($values)) $values = explode('|',$values);

            $query->where(function($query)use($values,$name){
                foreach($values as $value){
                    $query->orWhere($name, $value);
                }
            });
        }
    }
    /**
     * Format results after search
     *
     * @param App\Models\Base $instance
     * @return mixed
     */
    public function formatResult($inst){
        $formated = [];

        foreach($this->columns->getColumns() as $name => $column){
            $formated[$name]=$column->formatValue($name,$inst);
        }

        return $formated;
    }
    /**
     * Run a function before search
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $values
     */
    public function beforeSearch($query,$values){

    }
    /**
     * Form validations
     *
     * @param array $values
     * @throws \App\Exceptions\WebAPIException
     */
    public function validate($values){
    }
    /**
     * Runing action before create
     *
     * @param array $values
     * @return array formated values
     */
    public function beforeCreate($values){
        return $values;
    }
    /**
     * Runing action before update
     *
     * @param array $values
     * @param Base $instance
     * @return array formated values
     */
    public function beforeUpdate($values,$instance){
        return $values;
    }
    /**
     * Runing an action after create
     *
     * @param \App\Models\Base $inst
     * @param array $values unformated validated values
     */
    public function afterCreate($inst,$values){

    }
    /**
     * Runing an action after updated
     *
     * @param \App\Models\Base $inst
     * @param array $values unformated validated values
     */
    public function afterUpdate($inst,$values){

    }
    /**
     * Running an action before deleting an item
     *
     * @param \App\Models\Base $inst
     * @return bool Weather that item is delete or not
     */
    public function beforeDelete($inst){

        return true;
    }
    /**
     * Runing an action after deleting an item
     *
     * @param int $id
     */
    public function afterDelete($id){

    }
    /**
     * Running an action before deleting an item
     *
     * @param \App\Models\Base $inst
     * @return bool Weather that item is restore or not
     */
    public function beforeRestore($inst){

        return true;
    }
    /**
     * Runing an action after deleting an item
     *
     * @param int $id
     */
    public function afterRestore($id){

    }
    /**
     * Returning the current form name
     *
     * @return string
     */
    public function getName(){
        $className = get_class($this);

        $formName = substr(strrchr($className,"\\"),1);

        return $formName;
    }


}
