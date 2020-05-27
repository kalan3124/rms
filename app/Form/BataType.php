<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BataType extends Form{

    protected $title='Bata Type';

    protected $dropdownDesplayPattern = 'bt_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('bt_name')->setLabel('Name');
        $inputController->text('bt_code')->setLabel('Code')->isUpperCase();
        $inputController->select('bt_type')->setLabel('Type')->setOptions([
            0=>'MAIN',
            1=>'FIELD MANAGER',
            2=>'MR/PS',
            3=>'SALES REP',
            4=>'JENG',
            5=>'SENG',
            6=>"DSR"
        ]);
        $inputController->number('bt_value')->setLabel('Value');
        $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("division");
        $inputController->ajax_dropdown("btc_id")->setLabel("Bata Category")->setLink("bata_category");
        $inputController->setStructure([['bt_name','bt_code'],['bt_type','bt_value'],['divi_id','btc_id']]);
    }

    public function beforeSearch($query,$values){
        $query->with('division','bata_category');
    }

    public function filterDropdownSearch($query, $where)
    {
        if(isset($where['user'])){
            $user = User::find($where['user']['value']);

            if($user){
                $query->where('divi_id',$user->divi_id);
                switch ($user->u_tp_id) {
                    case 3:
                        $query->where('bt_type',2);
                        break;
                    case 2:
                        $query->where('bt_type',1);
                        break;
                    case 10:
                        $query->where('bt_type',3);
                        break;
                    case 15:
                        $query->where('bt_type',6);
                        break;
                    default:
                        break;
                }
            }
        }

        unset($where['user']);

        if(isset($where['users'])) {
            $query->where('bt_type',3);
            unset($where['users']);
        }

        parent::filterDropdownSearch($query,$where);
    }

}