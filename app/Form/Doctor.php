<?php
namespace App\Form;
use App\Form\Columns\ColumnController;
use App\Models\DoctorInstitution;
use App\Models\DoctorSubTown;
// use App\Models\Doctor;

Class Doctor Extends Form{

    protected $title ='Doctor';

    /**
     * Filtering search results in dropdown
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $where
     * @return void
     */
    public function filterDropdownSearch($query,$where){

        if(isset($where['sub_twn_id'])){
            $subTownId = $where['sub_twn_id'];
            unset($where['sub_twn_id']);

            $query->whereIn('doc_id',function($querySubTown)use($subTownId){
                $querySubTown->select('doc_id')
                    ->from((new DoctorSubTown)->getTable())
                    ->where('sub_twn_id',$subTownId)
                    ->whereNull('deleted_at');
            });
            
        }

        foreach ($where as $name => $values) {

            if(!is_array($values)) $values=[$values];

            $query->where(function($query)use($values,$name){
                foreach($values as $value){
                    $query->orWhere($name, $value);
                }
            });
        }
    }

    protected $dropdownDesplayPattern = 'doc_name - doctor_speciality.speciality_short_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('doctor_speciality');
    }
    public function beforeSearch($query,$values){
        $query->with('doctor_speciality','doctor_class');

        if($values['approved_at']['value'] == 1){
            $query->whereNull('approved_at');
        }elseif($values['approved_at']['value'] == 2){
            $query->whereNotNull('approved_at');
        }
        
    }

    public function afterCreate($inst,$values){
        if(is_array($values['institutions']))
        foreach ($values['institutions'] as  $institution) {
            DoctorInstitution::create([
                'ins_id'=>$institution['value'],
                'doc_id'=>$inst->getKey()
            ]);
        }

        if(is_array($values['sub_twns']))
            foreach($values['sub_twns'] as $subTown){
                DoctorSubTown::create([
                    'sub_twn_id'=>$subTown['value'],
                    'doc_id'=>$inst->getKey()
                ]);
            }
    }
     
    public function afterUpdate($inst, $values)
    {
        $this->afterDelete($inst->getKey());
        $this->afterCreate($inst,$values);
    }

    public function afterDelete($id)
    {
        DoctorSubTown::where('doc_id',$id)->delete();
        DoctorInstitution::where('doc_id',$id)->delete();
    }

    public function formatResult($inst){
        $formated = [];

        foreach($this->columns->getColumns() as $name => $column){
            if($name!='institutions'&&$name!='sub_twns')
                $formated[$name]=$column->formatValue($name,$inst);
        }

        $institutions = DoctorInstitution::with('institution')->where('doc_id',$inst->getKey())->get();
        $institutions->transform(function($doctorInsitution){
            return [
                'label'=>$doctorInsitution->institution->ins_name,
                'value'=>$doctorInsitution->institution->getKey()
            ];
        });

        $subTowns = DoctorSubTown::with('subTown')->where('doc_id',$inst->getKey())->get();
        $subTowns->transform(function($doctorSubTown){
            if(!isset($doctorSubTown->subTown))
                return null;
            return [
                'label'=>$doctorSubTown->subTown->sub_twn_name,
                'value'=>$doctorSubTown->subTown->getKey()
            ];
        });

        $subTowns = $subTowns->filter(function($subTown){
            return !!$subTown;
        });

        $formated['sub_twns'] = $subTowns->values();
        $formated['institutions'] = $institutions;

        $formated['approved_at'] = ($inst->approved_at)?$inst->approved_at->format('Y-m-d'):'Unapproved';

        return $formated;
    }

    protected function setColumns(ColumnController $columnController){
        foreach($this->inputs->getOnlyPrivilegedInputs() as $name=>$input){
            if($input->getType()!='date')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
            else 
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input)->setSearchable(false);
        }
        
        $columnController->date('approved_at')->setLabel("Approved Date");
        $columnController->getColumn('institutions')->setSearchable(false)->setType('multiple_ajax_dropdown');
        $columnController->getColumn('approved_at')->setSearchable(false);

        $columnController->date('created_at')->setLabel("Created Date");
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('doc_name')->setLabel('Doctor Name');
        $inputController->ajax_dropdown('doc_spc_id')->setLabel('Doctor Specification')->setLink('doctor_speciality');
        $inputController->text('slmc_no')->setLabel('SLMC Number');
        $options = [
            1=>'Male',
            2=>'Female'
        ];
        $inputController->select('gender')->setLabel('Gender')->setOptions($options);
        $inputController->date('date_of_birth')->setLabel('Date Of Birth');
        $inputController->number('phone_no')->setLabel('Phone Number')->setValidations('required||min:6||max:11');
        $inputController->number('mobile_no')->setLabel('Mobile Number')->setValidations('required||min:6||max:11');
        $inputController->ajax_dropdown('doc_class_id')->setLabel('Doctor Class')->setLink('doctor_class');

        $inputController->ajax_dropdown('institutions')->setLabel('Institutions')->setLink('institution')->setMultiple();
        $inputController->ajax_dropdown('sub_twns')->setLabel('Sub Town')->setLink('sub_town')->setMultiple();
        $inputController->text('doc_code')->setLabel('Code');

        $options = [
            1=>'Not Apprved',
            2=>'Approved'
        ];
        $inputController->select('approved_at')->setLabel('Approved Status')->setOptions($options);
    
        $inputController->setStructure([
            ['doc_name','slmc_no','doc_code'],
            ['doc_spc_id','doc_class_id'],
            ['date_of_birth','gender'],
            ['phone_no','mobile_no'],
            ['sub_twns','institutions','approved_at']
        ]);  
    }

     
}