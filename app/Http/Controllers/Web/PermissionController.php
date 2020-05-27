<?php
namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\Permission;
use App\Models\UserPermission;
use App\Http\Controllers\Controller;

class PermissionController extends Controller{

    public function loadItems(){
        $subJson = Storage::get('/menu/sub.json');
        $sections = json_decode($subJson,true);

        $formatedSubMenus = [
            'medical'=>[],
            'sales'=>[],
            'common'=>[],
            'distributor'=>[]
        ];


        foreach($sections as $sectionId => $subMenus){
            foreach ($subMenus as $subMenuId => $subMenu) {

                $formatedSubMenu = [];

                foreach ($subMenu['items'] as $itemId => $item) {
                    $formatedSubMenu[$itemId] = [
                        'id'=>$sectionId.'.'.$subMenuId.'.'.$itemId,
                        'title'=>$item['title'],
                        'hasActions'=>substr($item['link'],0,6)=='panel/'
                    ];
                }
    
                $formatedSubMenus[$sectionId][$subMenuId] = [
                    'title'=>$subMenu['title'],
                    'items'=>$formatedSubMenu
                ];
            }

        }


        return $formatedSubMenus;
    }
    /**
     * Saving user permissions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function save(Request $request){
        $validation = Validator::make($request->all(),[
            'users'=>'array',
            'users.*.value'=>'required|numeric|exists:users,id',
            'permissionValues'=>'required|array',
            'permissionGroups'=>'array',
            'permissionGroups.*.value'=>'required|numeric|exists:permission_group,pg_id'   
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $users = $request->input('users');
        $permissionValues = $request->input('permissionValues');
        $permissionGroups = $request->input('permissionGroups');

        foreach ($users as  $user) {
            UserPermission::where('u_id',$user['value'])->delete();
        }

        foreach ($permissionGroups as  $group) {
            UserPermission::where('pg_id',$group['value'])->delete();
        }

        foreach($permissionValues as $permission_code){
            $permission = Permission::firstOrCreate([
                'perm_code'=>$permission_code
            ]);

            foreach ($users as  $user) {

                UserPermission::create([
                    'perm_id'=>$permission->getKey(),
                    'u_id'=>$user['value'],
                ]);
            }

            foreach ($permissionGroups as  $group) {

                UserPermission::create([
                    'perm_id'=>$permission->getKey(),
                    'pg_id'=>$group['value'],
                ]);
            }
        }

        return response()->json([
            "success"=>true,
            'message'=>"Successfully updated the permissions."
        ]);
    }
    /**
     * Loading available permissions for a user or permission group
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loadByUser(Request $request){
        $validation = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|numeric',
            'type'=>'required'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invali request");
        }

        $type = $request->input('type');

        $user = $request->input('user');

        $where = [];

        if($type=='user')
            $where['u_id'] = $user['value'];
        else
            $where['pg_id'] = $user['value'];

        $permissions = UserPermission::with('permission')->where($where)->get();

        $permissions->transform(function($userPermission){
            return $userPermission->permission->perm_code;
        });

        return $permissions;
    }
}