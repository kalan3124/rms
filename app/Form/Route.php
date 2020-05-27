<?php
namespace App\Form;

use App\Models\Area;
use App\Models\DistributorCustomer;
use App\Models\DistributorSalesRep;
use App\Models\DistributorSrCustomer;
use App\Models\Route as ModelsRoute;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Auth;

class Route extends Form{

    protected $title = 'Route';

    protected $dropdownDesplayPattern = 'route_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('area');
    }

    public function beforeSearch($query,$values){
        $query->with('area','area.region');
        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();

            if($area){
                $query->where('ar_id',$area->getKey());
            }
        }

        if($user->getRoll() == config('shl.distributor_type')){
            $dsrs = DistributorSalesRep::whereIn('dis_id',[$user->getKey()])->get();
            $chemist = DistributorSrCustomer::whereIn('u_id',$dsrs->pluck('sr_id')->all())->get();
            $routes = DistributorCustomer::whereIn('dc_id',$chemist->pluck('dc_id')->all())->get();
            $query->whereIn('route_id',$routes->pluck('route_id')->all());
        }
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('seq_no')->setLabel('Seq No');
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area');
        $inputController->text('route_code')->setLabel('Route Code')->isUpperCase();
        $inputController->text('route_name')->setLabel('Route Name');
        $inputController->number('route_schedule')->setLabel('Route Schedule (Sales Rep)')->setValidations('');
        $inputController->select('route_type')->setLabel("Route Type")->setOptions([
           0=>'Sales Rep',
           1=>'Distributor',
           2=>'DC'
        ]);

        $inputController->setStructure([
            ['seq_no',
            'ar_id'],
            ['route_code',
            'route_name'],
            ['route_schedule',
            'route_type']
        ]);
    }

    public function filterDropdownSearch($query,$where){
        $user = Auth::user();

        if(isset($where['u_id'])){
            $user = UserModel::find($where['u_id']['value']);

            if($user){
                $userCode = substr($user->u_code,0,4);

                $area = Area::where('ar_code',$userCode)->first();

                if($area){
                    $query->where('ar_id',$area->getKey());
                }
            }
            unset($where['u_id']);
        }

        if(isset($where['users'])){

            $codes = [];

            $user = UserModel::find($where['users'][0]['value']);

            if($user->u_tp_id==config('shl.sales_rep_type')){
                foreach ($where['users'] as $key => $user) {
                    $user = UserModel::find($user['value']);

                    $codes[] = substr($user->u_code,0,4);
                }

                $areas = Area::whereIn('ar_code',$codes)->get();

                $query->whereIn('ar_id',$areas->pluck('ar_id')->all());
            }

            unset($where['users']);
        }

        parent::filterDropdownSearch($query,$where);
    }

}
