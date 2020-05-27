<?php

namespace App\Form;

use App\Form\Columns\ColumnController;
use App\Models\DistributorSalesRep;
use App\Models\DistributorSrCustomer;
use App\Models\SalesPriceLists;
use App\Model\DistributorCustomerClass;
use App\Model\DistributorCustomerSegment;
use App\Exceptions\WebAPIException;
use App\Models\DistributorCustomer as ModelsDistributorCustomer;

class DistributorCustomer extends Form
{

    protected $title = 'Distributor Customer';

    protected $dropdownDesplayPattern = 'dc_name';

    public function beforeSearch($query, $values)
    {
        $query->with('sub_town', 'sub_town.town', 'sales_price_lists','distributor_customer_class','distributor_customer_segment');
    }


    public function beforeCreate($values)
    {
        if (isset($values['location'])) {
            $values['dc_lat'] = $values['location']['lat'];
            $values['dc_lng'] = $values['location']['lng'];
            unset($values['location']);
        }


        if (!isset($values['price_group'])) {
            $values['price_group'] = config('shl.pg01_auto_increment_id');
            // 21853
        }

        if (!isset($values['dcc_id'])) {
            $values['dcc_id']['dcc_name'];
        }

        if (!isset($values['dcs_id'])) {
            $values['dcs_id']['dcs_name'];
        }

        $disCus = ModelsDistributorCustomer::where('dc_code',$values['dc_code'])->first();

        if (isset($disCus)) {
            throw new WebAPIException('Customer Code Already Used.Please use the different one');
        }

        return $values;
    }

    public function beforeUpdate($values, $instance)
    {
        if (isset($values['location'])) {
            $values['dc_lat'] = $values['location']['lat'];
            $values['dc_lon'] = $values['location']['lng'];
            unset($values['location']);
        }

        return $values;
    }

    public function formatResult($inst)
    {
        $formated = [];

        foreach ($this->columns->getColumns() as $name => $column) {
            if ($name !== 'location')
                $formated[$name] = $column->formatValue($name, $inst);
        }

        $formated['dc_image_url'] = $inst->dc_image_url;

        $formated['location'] = $inst->dc_lat && $inst->dc_lon ? [
            'lat' => (float) $inst->dc_lat,
            'lng' => (float) $inst->dc_lon
        ] : null;

        return $formated;
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('dc_name')->setLabel('Name');
        $inputController->text('dc_code')->setLabel('Code')->isUpperCase();
        $inputController->text('dc_address')->setLabel('Address');
        $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setLink('sub_town');
        $inputController->ajax_dropdown('price_group')->setLabel('Price Group (PG01)')->setLink('sales_price_lists')->setValidations('');
        $inputController->ajax_dropdown('dcc_id')->setLabel('Distributor Customer Class')->setLink('distributor_customer_class');
        $inputController->ajax_dropdown('dcs_id')->setLabel('Distributor Customer Segment')->setLink('distributor_customer_segment');
        $inputController->check('dc_is_vat')->setLabel('Vat Enabled')->setValidations('');
        $inputController->image('dc_image_url')->setLabel('Image');
        $inputController->location('location')->setLabel('Location');

        $inputController->setStructure([
            ['dc_name', 'dc_code'],
            ['dc_address', 'sub_twn_id', 'price_group'],
            ['dcc_id', 'dcs_id','dc_is_vat'],
            ['dc_image_url'],
            ['location']
        ]);
    }

    protected function setColumns(ColumnController $columnController)
    {
        foreach ($this->inputs->getOnlyPrivilegedInputs() as $name => $input) {
            if ($input->getType() != 'password' && $name != 'location')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
        }
        
        $columnController->location('location')->setLabel('Location')->setSearchable(false)->setRenderer(function($value){
            if(!$value)
                return "";
            return $value['lat'].','.$value['lng'];
        });

        $columnController->date('created_at')->setLabel("Created Date");
    }

    public function filterDropdownSearch($query, $where)
    {
        if (isset($where['dis_id'])) {
            $DSRs = DistributorSalesRep::where('dis_id', $where['dis_id'])->get();

            $customers = DistributorSrCustomer::whereIn('u_id', $DSRs->pluck('sr_id')->all())->get();

            $query->whereIn('dc_id', $customers->pluck('dc_id')->all());

            unset($where['dis_id']);
        }

        parent::filterDropdownSearch($query, $where);
    }
}
