<?php 
namespace App\Form;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use App\Exceptions\WebAPIException;
use App\Models\User;
use Psr\Http\Message\ResponseInterface;
use Http\Client\Exception\RequestException;

Class Issue Extends Form {

    protected $title='Issue';

    protected $dropdownDesplayPattern = '';

    public function beforeSearch($query,$keyword){
        $query->with('user');
    }

    public function boot(){
        unset($this->actions['delete']);
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->ajax_dropdown('u_id')->setLink('user')->setLabel('User');
        $inputController->date('i_due_date')->setLabel('Due Date');
        $inputController->date('i_cmplt_date')->setLabel('Complete Date');
        $options = [
            1=>"App",
            2=>"Backend",
            3=>"App/Backend"
        ];

        $inputController->select('i_application')->setLabel('Application')->setOptions($options);
        $inputController->text('i_module')->setLabel("Module");
        $inputController->text('i_description')->setLabel("Description");
        $inputController->text('i_cmnt_shl')->setLabel("SHL Comment");
        $inputController->text('i_cmnt_cl')->setLabel("CL Comment");
        $inputController->select('i_status')->setLabel("Status")->setOptions([
            0=>"Pending",
            1=>"Completed",
            2=>"Issue Occured"
        ]);
        $inputController->select('i_label')->setLabel("Label")->setOptions([
            0=>"Issue",
            1=>"New Feature"
        ]);

        $inputController->setStructure([
          ['i_application','i_module'],
          'i_description',
          ['u_id','i_due_date','i_cmplt_date'],
          ['i_cmnt_shl','i_cmnt_cl'],
          ['i_status','i_label']
        ]);
    }

    /**
     * Making a request to git lab
     *
     * @param string $method
     * @param string $url
     * @param string|array $data
     * @param string $type
     * 
     * @return ResponseInterface
     */
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

        $options[$formatedType]=$data;

        if($method=="POST"){
            $options['headers']['Content-Type']= $type=="json"?'application/json':'multipart/form-data';
            $options['headers']['Accept']= 'application/json';
        }

        try{
            $response = $client->request($method,config("gitlab.server")."/projects/$projectId/".$url,$options);
        } catch (RequestException $e ){
            throw new WebAPIException("Internel server error!");
        }

        return $response;
    }

    public function afterCreate($inst, $values)
    {
        $content = "";
        $title = "";

        if($inst->i_label){
            $label="New Feature";
        } else {
            $label = "Issue";
        }

        if(!empty(trim($inst->i_application))){

            switch ($inst->i_application) {
                case 1:
                    $content.= "Application:- App\n\n";
                    break;
                case 2:
                    $content.= "Application:- Backend\n\n";
                    break;
                case 3:
                    $content.= "Application:- App/Backend\n\n";
                    break;
                default:
                    $content.= "Application:- Unknown\n\n";
                    break;
            }

        }

        if(!empty(trim($inst->i_module))){
            $title.="[ ".$inst->i_module.' ] ';
            $content.="Module:- ".$inst->i_module."\n\n";
        }


        if(!empty(trim($inst->u_id))){
            $user = User::find($inst->u_id) ;
            if($user){
                $content.="User:- ".$user->name." [".$user->u_code."]\n\n";
            }
        }

        if(!empty(trim($inst->i_description))){
            $title.=$inst->i_description;
            $content.="Desciption:- ".$inst->i_description."\n\n";
        }

        if(!empty(trim($inst->i_cmnt_shl))){
            $content.="Comment:- ".$inst->i_cmnt_shl."\n\n";
        }

        $data = [
            "title"=>$inst->i_description,
            "description"=>$content,
            "labels"=>$label
        ];

        if(!empty(trim($inst->i_due_date))){
            $data['due_date']= $inst->i_due_date;
        }

            
        $response = $this->makeRequest("POST","issues",$data);
        $body = $response->getBody()->getContents();

        $body = json_decode($body,true);

        $inst->i_num = $body['id']-1;

        $inst->save();
    }

    public function beforeUpdate($values,$instance)
    {
        $data = [];

        if($instance->i_status!=$values['i_status']){
            $data['state_event'] = $values['i_status']==1?"close":"reopen";
        }

        if($values['i_label']&&$instance->i_label!=$values['i_label']){
            $data['labels']= $values['i_label']?"New Feature":"Issue";
        }

        if( 
            !empty($instance->i_due_date)&& 
            !empty($values['i_due_date'])&&
            date('Y-m-d', strtotime( $instance->i_due_date))!=date('Y-m-d', strtotime( $values['i_due_date']))
        ){
            $data['due_date']= date('Y-m-d', strtotime( $values['i_due_date']));
        }

        if(!empty($data))
            $this->makeRequest("PUT","issues/".$instance->i_num,$data);

        if(trim($instance->i_cmnt_shl)!=trim($values['i_cmnt_shl'])&&!empty(trim($values['i_cmnt_shl']))){
            $this->makeRequest("POST","issues/".$instance->i_num."/notes",['body'=>$values['i_cmnt_shl']]);
        }

        return $values;
    }
}