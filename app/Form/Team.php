<?php
namespace App\Form;

use App\Models\TeamUser;
use App\Models\TeamProduct;
use App\Form\Columns\ColumnController;
use App\Models\Team as ModelsTeam;
use App\Models\UserTeam;
use Illuminate\Support\Facades\Auth;

class Team extends Form{

    protected $title='Team';

    protected $dropdownDesplayPattern = 'tm_name - tm_code';

    public function beforeSearch($query, $values)
    {
        $query->with(['user','user.user_type','head_of_department','division']);

        $this->beforeDropdownSearch($query,'');
    }

    public function beforeDropdownSearch($query, $keyword)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if($user->getRoll()==config('shl.field_manager_type')){
            $query->where('fm_id',$user->getKey());
    
        } else if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

            if($teamUser){
                $query->where('tm_id',$teamUser->tm_id);
            }
        } else if($user->getRoll()==config('shl.head_of_department_type')) {
            $team = ModelsTeam::where('hod_id',$user->getKey())->get();
            $teamsIds = $team->pluck('tm_id')->all();

            // if($teamsIds){
            //     $query->whereIn('tm_id',$teamsIds);
            // }

            $query->whereIn('tm_id',$teamsIds);
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $query->whereIn('tm_id',$teams->pluck('tm_id')->all());
        }  
    }

    public function afterCreate($inst, $values)
    {
        if(isset($values['members'])){
            foreach($values['members'] as $member){
                TeamUser::create([
                    'tm_id'=>$inst->getKey(),
                    'u_id'=>$member['value']
                ]);
            }
        }
    }

    public function afterUpdate($inst, $values)
    {
        $teamUsers = TeamUser::where('tm_id',$inst->getKey())->get();

        $userIds = array_column($values['members'],'value');

        $deletingUsers = $teamUsers->whereNotIn('u_id',$userIds);

        TeamUser::whereIn('tmu_id',$deletingUsers->pluck('tmu_id'))->delete();

        foreach($values['members'] as $member){
            if(!$teamUsers->contains('u_id',$member['value']))
                TeamUser::create([
                    'tm_id'=>$inst->getKey(),
                    'u_id'=>$member['value']
                ]);
        }
    } 

    public function afterDelete($id)
    {
        TeamProduct::where('tm_id',$id)->delete();
        TeamUser::where('tm_id',$id)->delete();
    }

    public function formatResult($inst){
        $formated = [];

        foreach($this->columns->getColumns() as $name => $column){
            if($name!='members'&&$name!='products')
                $formated[$name]=$column->formatValue($name,$inst);
        }

        $members = TeamUser::with('user')->where('tm_id',$inst->getKey())->get();
        $members->transform(function($member){
            if($member->user)
                return [
                    'label'=>$member->user->name,
                    'value'=>$member->user->getKey()
                ];
            else return null;
        });

        $members = $members->filter(function($member){return !!$member;});

        $products = TeamProduct::with('product')->where('tm_id',$inst->getKey())->get();
        $products->transform(function($product){
            if(!$product->product) return null;

            return [
                'label'=>$product->product->product_name,
                'value'=>$product->product->getKey()
            ];
        });

        $products = $products->filter(function($product){return !!$product;});

        $formated['members']= $members->values();
        $formated['products']= $products->values();

        return $formated;
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {

        $inputController->text('tm_name')->setLabel('Team Name');
        $inputController->text('tm_code')->setLabel('Team Code')->isUpperCase();
        $inputController->ajax_dropdown('divi_id')
            ->setLabel('Division')
            ->setLink('division');
        $inputController->ajax_dropdown('fm_id')
            ->setLabel('Field Manager')
            ->setLink('user')
            ->setWhere([
                'u_tp_id'=>config('shl.field_manager_type')
            ]);
        $inputController->ajax_dropdown('members')
            ->setLabel('MR/PS')
            ->setLink('user')
            ->setWhere([
                'u_tp_id'=>config('shl.medical_rep_type').'|'.config('shl.product_specialist_type')
            ])
            ->setMultiple();

        $inputController->ajax_dropdown('hod_id')
            ->setLabel('Head Of Department')
            ->setLink('head_of_department')
            ->setWhere([
                'u_tp_id'=>config('shl.head_of_department_type')
            ]);
        $inputController->date('tm_exp_block_date')->setLabel('Expenses & Itinerary Blocking Date');
        $inputController->number('tm_mileage_limit')->setLabel('Team Mileage Percentage')->setValidations('');
        $inputController->setStructure([
            ['tm_name','tm_code'],
            ['divi_id','fm_id','hod_id'],
            ['members'],
            ['tm_exp_block_date',['tm_mileage_limit']]
        ]);
    }

    /**
     * Setting table columns
     * 
     * @param ColumnController $columnController
     * @return void
     */
    protected function setColumns(ColumnController $columnController){
        foreach($this->inputs->getOnlyPrivilegedInputs() as $name=>$input){
            if($input->getType()!='password'){
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
            }
        }

        $columnController->getColumn('members')->setType('multiple_ajax_dropdown')->setSearchable(false);
        $columnController->getColumn('tm_exp_block_date')->setSearchable(false);
        $columnController->multiple_ajax_dropdown('products')
        ->setLabel('Allocated Products')->setSearchable(false);

        $columnController->date('created_at')->setLabel("Created Date");
    }

}