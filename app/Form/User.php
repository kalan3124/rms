<?php

namespace App\Form;

use App\Models\TeamUser;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use App\Models\User as UserModel;
use App\Models\Expenses;
use App\Models\Limit;
use App\Models\UserArea as UserAreaModel;
use App\Models\Area;
use App\Models\DeletionHistory;
use App\Models\DistributorSalesMan;
use App\Models\DistributorSalesRep;
use App\Models\UserTeam;

class User extends Form
{

    protected $title = 'User';

    protected $dropdownDesplayPattern = 'name - u_code';

    public function beforeSearch($query, $values)
    {
        $query->with(['user_type', 'division', 'vehicle_type']);

        $user = Auth::user();
        /** @var \App\Models\User $user */

        if (in_array($user->getRoll(), [
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])) {
            $users = UserModel::getByUser($user);
            $query->whereIn('id', $users->pluck('u_id')->all());
        }

        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $products = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('id',$products->pluck('u_id')->all());
        }

        if (isset($values['tm_id'])) {
            $team = Team::with('teamUsers')->where('tm_id', $values['tm_id'])->first();
            $userIds = $team->teamUsers->pluck('u_id');
            if (isset($team->fm_id))
                $userIds->push($team->fm_id);

            if ($team) {
                $query->whereIn('id', $userIds->all());
            }
            unset($values['tm_id']);
        }

        $query->withTrashed();
    }

    public function beforeDropdownSearch($query, $keyword)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if ($user->getRoll() == config('shl.field_manager_type')) {
            $teams = Team::where('fm_id', $user->getKey())->with(['teamUsers'])->latest()->first();

            $userIds = $teams->teamUsers->pluck('u_id')->all();

            $userIds[] = $user->getKey();

            $query->whereIn('id', $userIds);
        } else if (in_array($user->getRoll(), [
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
        ])) {
            $userIds = [$user->getKey()];

            $query->whereIn('id', $userIds);
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('id',$users->pluck('u_id')->all());
        }
    }

    public function afterCreate($inst, $values)
    {
        if ($values['u_tp_id'] == config('shl.medical_rep_type') || $values['u_tp_id'] == config('shl.product_specialist_type')) {
            if (!empty($values['tm_id']) && trim($values['tm_id']) != "") {
                TeamUser::create([
                    'tm_id' => $values['tm_id'],
                    'u_id' => $inst->getKey()
                ]);
            }
        }

        if (isset($values['u_pvt_mileage_limit']) && $values['u_pvt_mileage_limit'] != 0) {
            Limit::create([
                'lmt_ref_id' => $inst->getKey(),
                'lmt_main_type' => 1,
                'lmt_sub_type' => 1,
                'lmt_min_amnt' => $values['u_pvt_mileage_limit'],
                'lmt_frequency' => 2,
                'lmt_start_at' => date('Y-m-d')
            ]);
        }

        if (isset($values['u_prking_limit']) && $values['u_prking_limit'] != 0) {
            Limit::create([
                'lmt_ref_id' => $inst->getKey(),
                'lmt_main_type' => 1,
                'lmt_sub_type' => 2,
                'lmt_min_amnt' => $values['u_prking_limit'],
                'lmt_frequency' => 1,
                'lmt_start_at' => date('Y-m-d')
            ]);
        }

        if (isset($values['u_ad_mileage_limit']) && $values['u_ad_mileage_limit'] != 0) {
            Limit::create([
                'lmt_ref_id' => $inst->getKey(),
                'lmt_main_type' => 1,
                'lmt_sub_type' => 3,
                'lmt_min_amnt' => $values['u_ad_mileage_limit'],
                'lmt_frequency' => 2,
                'lmt_start_at' => date('Y-m-d')
            ]);
        }

        DeletionHistory::create([
            'dh_table_name'=> 'users',
            'dh_primary_key'=> $inst->getKey() ,
            'dh_from_date'=> date('Y-m-d'),
            'dh_to_date'=> null,
        ]);

        //set current date
        $inst->u_password_created = date('Y-m-d');
        $inst->fail_attempt = 0;
        $inst->save();
    }

    public function afterUpdate($inst, $values)
    {
        TeamUser::where('u_id', $inst->getKey())->delete();
        Expenses::where('u_id', $inst->getKey())->delete();
        $this->afterCreate($inst, $values);
    }

    public function afterDelete($id)
    {
        $deletionHistory = DeletionHistory::where('dh_table_name','users')
            ->where('dh_primary_key',$id)
            ->whereNull('dh_to_date')
            ->latest()
            ->first();

        if($deletionHistory){
            $deletionHistory->dh_to_date = date('Y-m-d');
            $deletionHistory->save();
        }
    }

    protected function setColumns(\App\Form\Columns\ColumnController $columnController)
    {
        foreach ($this->inputs->getOnlyPrivilegedInputs() as $name => $input) {
            if ($input->getType() != 'password')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
        }
        // $columnController->ajax_dropdown('tm_id')->setLabel("Team")->setSearchable();
        $columnController->date('created_at')->setLabel("Created Date");
    }

    public function formatResult($inst)
    {
        $formated = [];

        foreach ($this->columns->getColumns() as $name => $column) {
            if ($name != 'tm_id')
                $formated[$name] = $column->formatValue($name, $inst);
        }

        $team = null;
        $teamUser = TeamUser::where('u_id', $inst->getKey())->with('team')->first();
        if ($teamUser && $teamUser->team) $team = [
            'label' => $teamUser->team->tm_name,
            'value' => $teamUser->tm_id
        ];

        $formated['tm_id'] = $team;

        $formated['deleted'] = !!$inst->deleted_at;

        return $formated;
    }

    public function filterDropdownSearch($query, $where)
    {
        $user = Auth::user();

        if (isset($where['tm_id'])) {
            $team = Team::with('teamUsers')->where('tm_id', $where['tm_id'])->first();
            $userIds = $team->teamUsers->pluck('u_id');
            if (isset($team->fm_id))
                $userIds->push($team->fm_id);

            if ($team) {
                $query->whereIn('id', $userIds->all());
            }
            unset($where['tm_id']);
        }

        if (isset($where['u_id'])) {

            $user = UserModel::find($where['u_id']['value']);

            if ($user) {
                $userCode = substr($user->u_code, 0, 4);

                $area = Area::where('ar_code', $userCode)->first();

                $UserArea = UserAreaModel::where('ar_id', $area->getKey())->get();

                $query->whereIn('id', $UserArea->pluck('u_id')->all())->where('u_tp_id', config('shl.area_sales_manager_type'));
            }

            unset($where['u_id']);
        }

        if (isset($where['users'])) {
            $codes = [];

            foreach ($where['users'] as $key => $user) {
                $user = UserModel::find($user['value']);

                $codes[] = substr($user->u_code, 0, 4);
            }

            $areas = Area::whereIn('ar_code', $codes)->get();

            $userAreas = UserAreaModel::where('ar_id', $areas->pluck('ar_id')->all())->get();

            $query->whereIn('id', $userAreas->pluck('u_id')->all())->where('u_tp_id', config('shl.area_sales_manager_type'));

            unset($where['users']);
        }

        if ($user->getRoll() == 13) {
            $userCode = substr($user->u_code, 0, 4);

            $area = Area::where('ar_code', $userCode)->first();

            if ($area) {
                $query->where('u_code', 'LIKE', '%' . $area->ar_code . '%');
            }
        }


        if (isset($where['dis_id'])) {
            $disId = isset($where['dis_id']['value']) ? $where['dis_id']['value'] : $where['dis_id'];

            $salesReps = DistributorSalesMan::where('dis_id', $disId)->get();

            $distributorSalesReps = DistributorSalesRep::where('dis_id', $disId)->get();

            $salesReps = $salesReps->concat($distributorSalesReps);

            $query->whereIn('id', $salesReps->pluck('sr_id'));

            unset($where['dis_id']);
        } else if ($user->getRoll() == config('shl.distributor_type')) {

            $disId = $user->getKey();
            $distributorSalesReps = DistributorSalesRep::where('dis_id', $disId)->get()->pluck('sr_id');
            $disId = $distributorSalesReps->push($disId);
            $query->whereIn('id', $disId);
        }

        if ($user->getRoll() == config('shl.head_of_department_type')) {
            $users = $user->getKey();
            $teams = Team::where('hod_id',$users)->get();
            $teams_users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $hod_users = $teams_users->pluck('u_id');
            $users = $hod_users->push($users);
            $users = $users->concat($teams->pluck('fm_id')->all());
            $query->whereIn('id',$users);
        }

        //drop down filter search
        parent::filterDropdownSearch($query, $where);
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('u_code')->setLabel('Employee No')->isUpperCase();
        $inputController->text('name')->setLabel('Full Name');
        $inputController->text('user_name')->setLabel("Username");
        $inputController->ajax_dropdown('u_tp_id')->setLabel('User Type')->setLink('user_type');
        $inputController->ajax_dropdown('tm_id')->setLabel('Team')->setLink("team")->setValidations('');
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink("division");
        $inputController->text('u_base_lov')->setLabel("Base");
        $inputController->ajax_dropdown('vht_id')->setLabel('Vehicle Type')->setLink("vehicle_type")->setValidations('');
        $inputController->select('price_list')->setLabel("Price List")->setOptions([1 => "Actual Price List", 2 => "Budget Price List"]);
        $inputController->email('email')->setLabel('Email')->setValidations('required||email');
        $inputController->number('contact_no')->setLabel('Contact Number')->setValidations('required||min:6||max:11');
        $inputController->password('password')->setLabel("Password")->setValidations('required||min:8||password');
        $inputController->number('base_allowances')->setLabel("Base Allowance")->setCustomProp('step', '0.01');
        $inputController->check('private_mileage')->setLabel("Private Mileage");
        // $inputController->number('day_mileage_limit')->setLabel("Day Mileage Limit")->setValidations('');

        // $inputController->check('vat')->setLabel("Vat Enabled");

        $inputController->text('u_pvt_mileage_limit')->setLabel('Private Limit');
        $inputController->text('u_prking_limit')->setLabel('Parking Limit');
        $inputController->text('u_ad_mileage_limit')->setLabel('Additional Limit');
        $inputController->setStructure([
            ['name', 'u_code'],
            ['user_name', 'password'],
            ['email', 'contact_no'],
            ['u_tp_id', 'vht_id'],
            ['divi_id', 'tm_id'],
            ['price_list', 'u_base_lov'],
            ['base_allowances', 'private_mileage'],
            ['u_pvt_mileage_limit', 'u_prking_limit', 'u_ad_mileage_limit']
        ]);
    }

    public function afterRestore($id)
    {
        DeletionHistory::create([
            'dh_table_name'=>'users',
            'dh_primary_key'=> $id,
            'dh_from_date'=> date('Y-m-d'),
            'dh_to_date'=>null
        ]);
    }
}
