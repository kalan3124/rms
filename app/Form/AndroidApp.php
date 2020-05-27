<?php
namespace App\Form;

use App\Exceptions\WebAPIException;
use App\Models\AndroidApp as BaseAndroidApp;


class AndroidApp extends Form{

    protected $title='Android App';

    public function beforeCreate($values)
    {
        $code = $values['aa_v_name'];
        $type = $values['aa_v_type'];

        if(round($code,0)!=$code) throw new WebAPIException("Insert an integer to the version code");

        $latestApp = BaseAndroidApp::where('aa_v_type',$type)->latest()->first();

        if($latestApp&&$latestApp->aa_v_name>=$code) throw new WebAPIException("Version code is smaller than the previous one.");

        return $values;
    }

    public function boot(){
        $update = $this->actions['update'];
        $delete = $this->actions['delete'];

        $delete->setPrivilegedUserRolls([0]);
        $update->setPrivilegedUserRolls([0]);
        
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('aa_v_name')->setLabel('Version Code');
        $inputController->text('aa_description')->setLabel('Version Name');
        $inputController->date_time('aa_start_time')->setLabel('Start Time');
        $inputController->file('aa_url')->setLabel('Android app')->setFileType('app');
        $options = [
            1=>'FFA',
            2=>'SFA',
            3=>'SFA-DIST'
        ];
        $inputController->select('aa_v_type')->setLabel('App Type')->setOptions($options);
        $inputController->setStructure([['aa_v_name',"aa_description"],['aa_v_type','aa_start_time'],'aa_url']);
    }

}