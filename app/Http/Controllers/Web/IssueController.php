<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Validator;
use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class IssueController extends Controller {
    public function create(Request $request ){

        $user = Auth::user();

        $validation = Validator::make($request->all(),[
            "content"=>"required|min:6"
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $content = $request->input("content");
        $label = $request->input("label","issue");

        if($label!="New Feature"&&$label!="Issue")
            $label="Issue";

        $content.= "\n\n User ID:- ".$user->getKey();
        $content.= "\n User Name:- ".$user->getName();

        $matches = [];
        preg_match("/([\#]+)\s(.*)\n/",$content,$matches);

        if(empty($matches)) throw new WebAPIException("Title not supplied to the issue.");

        $title = $matches[2];

        if($title=="Issue Title")
            throw new WebAPIException("Please provide a title for the issue");

        $explodedContent = explode('\n',$content,1);

        if(isset($explodedContent[1]))
            $content=$explodedContent[1];
        else 
            $content="";

            
        $this->makeRequest("POST","issues",[
            "title"=>$title,
            "description"=>$content,
            "labels"=>$label,
            "assignee_ids"=>config("gitlab.current_devs")
        ]);

        return response()->json([
            "success"=>true,
            "message"=>"Successfully submited the issue."
        ]);
    }

    protected function makeRequest($method="GET",$url,$data,$type="json"){
                
        $mainConfig = [];
        
        if(config("shl.proxy_address")){
            $mainConfig['proxy'] = config("shl.proxy_address");
        }

        $client = new Client($mainConfig);

        $projectId = config("gitlab.project_id");

        $formatedType = $type=='json'?RequestOptions::JSON:RequestOptions::FORM_PARAMS;

        $options = [
            'headers' => [
                'PRIVATE-TOKEN'=> config("gitlab.access_token")
            ]
        ];

        if($method=="POST"){
            $options[$formatedType]=$data;
            $options['headers']['Content-Type']= $type=="json"?'application/json':'multipart/form-data';
            $options['headers']['Accept']= 'application/json';
        }

        try{
            $response = $client->request($method,config("gitlab.server")."/projects/$projectId/".$url,$options);
        } catch (\Exception $e ){
            throw $e;
            throw new WebAPIException("Internel server error!");
        }

        return $response;
    }

    protected function formatDate($date){
        if(!$date) return null;
        return date("Y-m-d H:i:s",strtotime($date));
    }

    public function search(Request $request){
        $validator = Validator::make($request->all(),[
            'page'=>'required|numeric',
            'state'=>'required|in:opened,closed'
        ]);

        if($validator->fails()){
            throw new WebAPIException($validator->errors()->first());
        }

        $page = $request->input('page');
        $state = $request->input("state");

        $response = $this->makeRequest("GET","issues?state=$state&page=$page",[],"form");

        $issues = collect(json_decode($response->getBody()->getContents(),true));

        $issues->transform(function($issue){
            $returnArr =  [
                'title'=>$issue['title'],
                'dueAt'=>$this->formatDate($issue['due_date']),
                'closedAt'=>$this->formatDate($issue['closed_at']),
                'closedBy'=>null,
                'createdAt'=>$this->formatDate($issue['created_at'])
            ];

            if($issue['closed_by']){
                $returnArr['closedBy'] = [
                    'name'=>$issue['closed_by']['name'],
                    'avatar'=>$issue['closed_by']['avatar_url']
                ];
            }

            $assigness = collect($issue['assignees']);

            $assigness->transform(function($assignee){
                return [
                    'name'=>$assignee['name'],
                    'avatar'=>$assignee['avatar_url']
                ];
            });

            $returnArr['assignees'] = $assigness;

            return $returnArr;
        });

        return response()->json([
            'success'=>true,
            'issues'=>$issues
        ]);
    }
}