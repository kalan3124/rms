<?php
namespace App\Form;

use App\Models\PermissionGroupUser;

class PermissionGroup extends Form{

    protected $title='Permission Group';


    protected $dropdownDesplayPattern = 'pg_name';


    public function afterUpdate($inst,$values){
        $users = $values['users'];

        PermissionGroupUser::where('u_id',$inst->getKey())->delete();

        foreach($users as $user){
            PermissionGroupUser::create([
                'pg_id'=>$inst->getKey(),
                'u_id'=>($user['type']=='user')?$user['u_id']:null,
                'u_tp_id'=>($user['type']=='user_type')?$user['u_tp_id']:null,
            ]);
        }
    }

    public function afterCreate($inst,$values){
        $users = $values['users'];

        foreach($users as $user){
            PermissionGroupUser::create([
                'pg_id'=>$inst->getKey(),
                'u_id'=>($user['type']=='user')?$user['u_id']:null,
                'u_tp_id'=>($user['type']=='user_type')?$user['u_tp_id']:null,
            ]);
        }
    }

    public function formatResult($inst){
        $formated = [];

        foreach($this->columns->getColumns() as $name => $column){
            $formated[$name]=$column->formatValue($name,$inst);
        }

        $users = PermissionGroupUser::with([
            'user','user.user_type','user_type'
        ])->where('pg_id',$inst->getKey())->get();

        $formated['users'] = $users->transform(function($user){

            if($user->user){
                return [
                    'type'=>'user',
                    'name'=>$user->user->name,
                    'id'=>'user_type-'.$user->user->user_type->getKey().'-'.$user->user->getKey(),
                    'u_id'=>$user->user->getKey()
                ];
            } else if($user->user_type){
                return [
                    'type'=>'user_type',
                    'name'=>$user->user_type->user_type,
                    'id'=>'user_type-'.$user->user_type->getKey(),
                    'u_tp_id'=>$user->user_type->getKey()
                ];
            }
        })->sortBy(function($user,$key){
            switch ($user['type']) {
                case 'user_type':
                    return 2;
                case 'user':
                    return 1;
            }
        })->values();

        return $formated;
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('pg_name')->setLabel('Name');
        $inputController->text('pg_code')->setLabel('Code');

        $inputController->tree_select('users')->setLabel("Users")->setHierarchy([
            "user_type"=>[
                "children"=>"user",
                "param"=>[
                    "u_tp_id"=>"{1}"
                ],
                "name"=>"User Types"
            ],
            "user"=>[
                "term"=>[
                    "u_tp_id"=>"{1}"
                ],
                "param"=>[
                    "u_id"=>"{2}"
                ],
                "name"=>"Users"
            ]
        ])->setParent('user_type');

        $inputController->setStructure([['pg_name','pg_code'],'users']);
    }
}