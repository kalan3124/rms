<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Storage;
use App\Models\PermissionGroupUser;
use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPermission;
use App\Http\Controllers\Controller;

class SidebarController extends Controller
{

    /**
     * Getting permissions for the logged user
     *
     */
    protected function getPermissions(){
        $user = Auth::user();

        if($user->getKey()==1) return collect([]);

        $userPermissions = UserPermission::with('permission')->where('u_id',$user->getKey())->get();

        if($userPermissions->isEmpty()){
            $permissionGroupUser = PermissionGroupUser::join('permission_group AS p','p.pg_id','permission_group_user.pg_id')
                    ->with(['permissionGroup','permissionGroup.userPermissions','permissionGroup.userPermissions','permissionGroup.userPermissions.permission'])
                    ->select(['permission_group_user.pg_id','permission_group_user.u_id','permission_group_user.u_tp_id','permission_group_user.created_at','permission_group_user.updated_at'])
                    ->latest('permission_group_user.created_at')
                    ->where(function($query)use ($user){
                        $query
                        ->where('u_id',$user->getKey())
                        ->orWhere('u_tp_id',$user->u_tp_id);
                    })
                    ->whereNull('permission_group_user.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->first();
            if(
                !isset($permissionGroupUser )||
                !isset($permissionGroupUser->permissionGroup)||
                $permissionGroupUser->permissionGroup->userPermissions->isEmpty()
            ){
                throw new WebAPIException("You haven't permissions for any form");
            }

            $userPermissions = $permissionGroupUser->permissionGroup->userPermissions;
        }

        $userPermissions->transform(function($userPermission){
            $code = $userPermission->permission->perm_code;

            $exploded = explode('.',$code);

            return [
                'main'=>$exploded[0].'.'.$exploded[1],
                'all'=>isset($exploded[2])?0:1,
                'child'=>isset($exploded[2])?$exploded[2]:null
            ];
        });

        return $userPermissions;
    }

    public function getAll()
    {
        $user = Auth::user();

        $userPermissions = $this->getPermissions();

        $json = Storage::get('/menu/sub.json');

        $menu = json_decode($json,true);

        $filteredMenu = [
            'medical'=>[],
            'sales'=>[],
            'common'=>[]
        ];

        foreach ($menu as $sectionId => $section) {

            foreach($section as $subMenuId=> $subMenu){
                $permission = $userPermissions->firstWhere('main',$sectionId.'.'.$subMenuId);

                if(isset($permission)||$user->getKey()==1){
                    if((isset($permission['all'])&&$permission['all']==1)||$user->getKey()==1) $filteredMenu[$sectionId][$subMenuId] = $subMenu;
                    else{
                        $filteredMenu[$sectionId][$subMenuId] = [
                            'title'=>$subMenu['title'],
                            'items'=>[],
                        ];

                        foreach($subMenu['items'] as $itemId =>$item){
                            $itemPermission =  $userPermissions->where('main',$sectionId.'.'.$subMenuId)->firstWhere('child',$itemId);

                            if(isset($itemPermission)||$user->getKey()==1){
                                $filteredMenu[$sectionId][$subMenuId]['items'][$itemId] = $item;
                            }
                        }
                    }
                }
            }

        }

        if(!isset($filteredMenu['other']))
            $filteredMenu['other'] = [
                'title'=>"Other",
                "items"=>[
                ]
            ];


        return response()->json($filteredMenu);
    }

    public function getMain()
    {
        $user = Auth::user();

        $subJson = Storage::get('/menu/sub.json');
        $subMenu = json_decode($subJson,true);

        $mainJson = Storage::get('/menu/main.json');
        $mainMenu = json_decode($mainJson,true);

        $formatedMenu = [
            'sales'=>[],
            'main'=>[],
            'common'=>[]
        ];

        $userPermissions = $this->getPermissions();

        foreach ($mainMenu as $sectionId => $sectionMenu) {

            foreach ($sectionMenu as $menu) {

                $formatedItems = [];

                foreach ($menu['items'] as $item ) {
                    $explodedQuery = explode('.',$item['item']);

                    $allPermissions = $userPermissions->where('main',$sectionId.'.'.$explodedQuery[0])->firstWhere('all',1);

                    $sectionsPermission = true;

                    if(isset($explodedQuery[1])){
                        $sectionsPermission = $userPermissions->where('main',$sectionId.'.'.$explodedQuery[0])->firstWhere('child',$explodedQuery[1]);
                        $link = $subMenu[$sectionId][$explodedQuery[0]]['items'][$explodedQuery[1]]['link'];
                    } else {
                        $sectionsPermission = $userPermissions->firstWhere('main',$sectionId.'.'.$explodedQuery[0]);
                        if($allPermissions||$user->getKey()==1){
                            $links = array_keys($subMenu[$sectionId][$explodedQuery[0]]['items']);
                            $link = $subMenu[$sectionId][$explodedQuery[0]]['items'][$links[0]]['link'];
                        } else if($sectionsPermission) {
                            $link = $subMenu[$sectionId][$explodedQuery[0]]['items'][$sectionsPermission['child']]['link'];
                        }
                    }

                    if($allPermissions||$sectionsPermission||$user->getKey()==1)
                        $formatedItems[] = [
                            "title"=>$item['title'],
                            'link'=>$sectionId.'/'.$explodedQuery[0].'/'.$link,
                            'id'=>$item['icon_id']
                        ];
                }

                $formatedMenu[$sectionId][] = [
                    'title'=>$menu['title'],
                    'items'=>$formatedItems
                ];

            }
        }


        $zeroFilteredMenu = [];

        foreach ($formatedMenu as $sectionId=> $sectionMenu) {
            $zeroFilteredMenu[$sectionId] = [];

            foreach ($sectionMenu as $key => $menu) {
                if(!empty($menu['items'])){
                    $zeroFilteredMenu[$sectionId][] = $menu;
                }
            }
        }

        return $zeroFilteredMenu;
    }
}
