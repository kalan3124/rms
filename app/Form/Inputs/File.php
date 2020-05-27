<?php
namespace App\Form\Inputs;

use App\Form\Formaters\FileFormater;

class File extends Input{

    use FileFormater;

    protected $type = 'file';

    public function setFileType($type){
        $this->setCustomProp('file_type',$type);
        return $this;
    }
}
