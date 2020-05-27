<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\UserArea;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Region;
use App\Models\SubTown;
use App\Models\Town;
use App\Traits\Territory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAreaController extends Controller
{
    use Territory;

    protected $structure = [
        'rg'=>'region',
        'ar'=>'area',
        'twn'=>'town',
        'sub_twn'=>'sub_town'
    ];

    public function getTerritoryLevels(){
        return [
            [
                "label"=>"Regions",
                "link"=>"region"
            ],
            [
                "label"=>"Areas",
                "link"=>"area"
            ],
            [
                "label"=>"Towns",
                "link"=>"town"
            ],
            [
                "label"=>"Sub Towns",
                "link"=>"sub_town"
            ]
        ];
    } 

    public function getDetailsForUser(Request $request){
        $validator = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|numeric|exists:users,id'
        ]);

        if($validator->fails()){
            throw new WebAPIException("Provide a user to fetch data.");
        }

        $user= $request->input('user');

        $user = User::find($user['value']);

        $areas = $this->getAllocatedTerritories($user);

        $formatedAreas = [
            'region'=>[],
            'area'=>[],
            'town'=>[],
            'sub_town'=>[],
            's'=>true
        ];

        foreach($areas as $area){
            $selected = false;
            $parentTypeKey = null;
            foreach ($this->structure as $key => $name) {
                if(isset($area->{'ua_'.$key.'_id'})||$selected){

                    $parentTypeName = "";

                    if(isset($parentTypeKey)){
                        $parentTypeName = " - ".$area->{$parentTypeKey.'_name'};
                    }

                    $formatedAreas[$name][$area->{$key.'_id'}] = [
                        'label'=>$area->{$key.'_name'}.$parentTypeName,
                        'value'=>$area->{$key.'_id'},
                        'type'=>$name
                    ];

                    $selected = true;
                }

                $parentTypeKey = $key;
            }
        }
        

        return [
            'levels'=>[
                [
                    "label"=>"Regions",
                    "link"=>"region"
                ],
                [
                    "label"=>"Areas",
                    "link"=>"area"
                ],
                [
                    "label"=>"Towns",
                    "link"=>"town"
                ],
                [
                    "label"=>"Sub Towns",
                    "link"=>"sub_town"
                ]
            ],
            'areas'=>$formatedAreas
        ];
    }

    public function create(Request $request){

        $validator = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|exists:users,id',
            'area'=>'required|array',
            'area.value'=>'required|numeric'
        ]);

        if($validator->fails()){
            throw new WebAPIException("Can not validate your request.");
        }

        $user = $request->input('user');
        $area = $request->input('area');

        $columns = [
            'province'=>'pv_id',
            'district'=>'dis_id',
            'region'=>'rg_id',
            'area'=>'ar_id',
            'town'=>'twn_id',
            'sub_town'=>'sub_twn_id',
        ];


        $data = [];

        foreach($columns as $type=>$column){

            if($type==$area['type']){ 
                $data[$column] = $area['value'];
            }

        }

        $data['u_id'] = $user['value'];

        UserArea::firstOrCreate($data);

        return [
            'success'=>true,
            'message'=>"Successfully added the ".$area['type']
        ];

    }

    public function remove(Request $request){
        $validator = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|exists:users,id',
            'area'=>'required|array',
            'area.value'=>'required|numeric'
        ]);

        if($validator->fails()){
            throw new WebAPIException("Can not validate your request.");
        }

        $user = $request->input('user.value');
        $area = $request->input('area.value');
        $input_type = $request->input('area.type');

        $ids = ['sub_twn_id','twn_id','ar_id','rg_id'];
        $relations = [
            'sub_town'=>['town','town.area','town.area.region'],
            'town'=>['area','area.region'],
            'area'=>['region'],
            'region'=>[]
        ];

        $types = ['sub_town','town','area','region'];
        $models = [SubTown::class,Town::class,Area::class,Region::class];

        $index = array_search($input_type,$types);
        $i = 0;
        $currentKey = $area;
        $currentObj = $models[$index]::with($relations[$input_type])->where($ids[$index],$area)->first();
        $deleted_ = false;

        // Removing process for parents
        for($i=$index; $i<count($types);$i++){
            $currentId = $ids[$i];
            $currentType = $types[$i];
            $currentModel = $models[$i];

            $parentRelation = isset($types[$i+1])?$types[$i+1]:null;
            $parentId = isset($ids[$i+1])?$ids[$i+1]:null;

            $currentKey = $currentObj->getKey();

            $deleted = UserArea::where($currentId,$currentKey)->where('u_id',$user)->delete();
            if($deleted)
                $deleted_ = true;

            $parentExist = false;

            if($parentRelation){
                $parentKey = $currentObj->{$parentId};

                $parentExist = UserArea::where('u_id',$user)->where($parentId,$parentKey)->count();
            }

            if($parentRelation&&(!$deleted||$parentExist)){
                $parentKey = $currentObj->{$parentId};

                $sibilings = $currentModel::where($currentId,'!=',$currentKey)->where($parentId,$parentKey)->get();
                
                if($parentExist||!$deleted_){
                    foreach ($sibilings as $key => $sibiling) {
                        UserArea::create([
                            'u_id'=>$user,
                            $currentId=>$sibiling->getKey()
                        ]);
                    }
                }
            }


            if($parentRelation){
                $currentObj = $currentObj->{$parentRelation};
                $currentKey = $currentObj->getKey();
            }
        }

        $currentType = $types[$index];
        $currentId = $ids[$index];

        // Removing process for childs
        for($i=0;$i<$index;$i++){
            $type = $types[$i];
            $id = $ids[$i];
            
            $query = DB::table('user_areas')->join($type,$type.'.'.$id,'user_areas.'.$id);

            for($j=$i+1; $j<count($models); $j++){
                $type = $types[$j];
                $id = $ids[$j];
                $prevType = $types[$j-1];

                $query->join($type,$type.'.'.$id,$prevType.'.'.$id);
            }

            $query->where($currentType.'.'.$currentId,$area)->where('user_areas.u_id',$user)->update([
                'user_areas.deleted_at'=>date("Y-m-d H:i:s"),
                'user_areas.updated_at'=>date("Y-m-d H:i:s")
            ]);
        }

        return [
            'success'=>true,
            'message'=>"Successfully removed the ".$area['type']
        ];
    }

    public function removeAll(Request $request){
        $userId = $request->input('user.value');

        if(!$userId)
            throw new WebAPIException("Invalid request sent. Please try again after refreshing your browser");

        UserArea::where('u_id',$userId)->delete();

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully deleted all customer allocations from the user"
        ]);
    }
}
