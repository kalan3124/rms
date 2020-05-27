<?php
namespace App\Form;

use App\Form\Columns\ColumnController;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\BonusFreeProduct;
use App\Models\BonusProduct;
use App\Models\BonusRatio;

class Bonus extends Form {

    protected $title = 'Bonus';

    protected $dropdownDesplayPattern = 'bns_code - bns_name';

    public function beforeSearch($query,$values){
        $query->with([
            'distributors',
            'distributors.distributor',
            'products',
            'products.product',
            'products',
            'products.product',
            'ratios',
            'excludes',
            'excludes.excludedBonus',
        ]);
    }

    public function afterCreate($inst, $values)
    {
        if(isset($values['distributors'])){
            foreach ($values['distributors'] as $key => $distributor) {
                BonusDistributor::create([
                    'dis_id'=>$distributor['value'],
                    'bns_id'=>$inst->getKey()
                ]);
            }
        }

        if(!isset($values['distributors'])||empty($values['distributors'])){
            $inst->bns_all = 1;
        } else {
            $inst->bns_all = 0;
        }

        if(isset($values['bns_products'])) {
            foreach ($values['bns_products'] as $key => $product) {
                BonusProduct::create([
                    'product_id'=>$product['value'],
                    'bns_id'=>$inst->getKey()
                ]);
            }
        }

        if(isset($values['bns_free_products'])) {
            foreach ($values['bns_free_products'] as $key => $product) {
                BonusFreeProduct::create([
                    'product_id'=>$product['value'],
                    'bns_id'=>$inst->getKey()
                ]);
            }
        }

        if(isset($values['bonus_lines'])&&isset($values['bonus_lines']['lines'])) {
            foreach ($values['bonus_lines']['lines'] as $key => $line) {
                BonusRatio::create([
                    'bns_id'=>$inst->getKey(),
                    'bnsr_min'=>$line['min'],
                    'bnsr_max'=>$line['max'],
                    'bnsr_purchase'=>$line['purchase'],
                    'bnsr_free'=>$line['free']
                ]);
            }
        }

        if(isset($values['bns_excludes'])) {
            foreach ($values['bns_excludes'] as $key => $exclude) {
                BonusExclude::firstOrCreate([
                    'bnse_bns_id'=>$exclude['value'],
                    'bns_id'=>$inst->getKey()
                ]);
            }

            foreach ($values['bns_excludes'] as $key => $exclude) {
                BonusExclude::firstOrCreate([
                    'bnse_bns_id'=>$inst->getKey(),
                    'bns_id'=>$exclude['value']
                ]);
            }
        }

        $inst->save();
    }

    public function afterUpdate($instance, $values)
    {
        
        BonusRatio::where('bns_id',$instance->getKey())->delete();
        BonusProduct::where('bns_id',$instance->getKey())->delete();
        BonusDistributor::where('bns_id',$instance->getKey())->delete();
        BonusFreeProduct::where('bns_id',$instance->getKey())->delete();
        BonusExclude::where('bns_id',$instance->getKey())->delete();

        $this->afterCreate($instance,$values);
    }


    public function formatResult($inst)
    {
        $formated = [];

        $manual_columns = ['bns_free_products','bns_products','distributors','gps_lines','bns_excludes'];

        foreach($this->columns->getColumns() as $name => $column){
            if(!in_array($name,$manual_columns))
                $formated[$name]=$column->formatValue($name,$inst);
        }

        $formated['distributors'] = $inst->distributors->map(function(BonusDistributor $bonusDistributor){
            return [
                'label'=>$bonusDistributor->distributor?$bonusDistributor->distributor->name:"DELETED",
                'value'=>$bonusDistributor->distributor?$bonusDistributor->distributor->getKey():0,
            ];
        });

        $formated['bns_free_products'] = $inst->freeProducts->map(function(BonusFreeProduct $bonusFreeProduct){
            return [
                'label'=>$bonusFreeProduct->product?$bonusFreeProduct->product->product_name:"DELETED",
                'value'=>$bonusFreeProduct->product?$bonusFreeProduct->product->getKey():0,
            ];
        });

        $formated['bns_products'] = $inst->products->map(function(BonusProduct $bonusProduct){
            return [
                'label'=>$bonusProduct->product?$bonusProduct->product->product_name:"DELETED",
                'value'=>$bonusProduct->product?$bonusProduct->product->getKey():0,
            ];
        });

        $formated['bonus_lines'] = [
            'lastId'=>$inst->ratios->count()-1,
            'lines'=>$inst->ratios->map(function( BonusRatio $item,$key){
                return [
                    'id'=>$key,
                    'min'=>$item->bnsr_min,
                    'max'=>$item->bnsr_max,
                    'purchase'=>$item->bnsr_purchase,
                    'free'=>$item->bnsr_free
                ];
            })
        ];

        $formated['bns_excludes'] = $inst->excludes->map(function(BonusExclude $bonusExclude){
            return [
                'label'=>$bonusExclude->excludedBonus->bns_name,
                'value'=>$bonusExclude->excludedBonus->getKey()
            ];
        });

        return $formated;
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('bns_name')->setLabel('Description');
        $inputController->text('bns_code')->setLabel('Code')->isUpperCase();
        $inputController->date('bns_start_date')->setLabel('Start Date');
        $inputController->date('bns_end_date')->setLabel('End Date');
        $inputController->ajax_dropdown('bns_free_products')
            ->setLabel('Free Products')
            ->setMultiple(true)
            ->setLink('product');

        $inputController->ajax_dropdown('bns_products')
            ->setLabel('Products')
            ->setMultiple(true)
            ->setLink('product');

        
        $inputController->ajax_dropdown('bns_excludes')
            ->setLabel('Exclude When')
            ->setMultiple(true)
            ->setLink('bonus');

        $inputController->ajax_dropdown('distributors')
            ->setLabel('Distributors (Optional)')
            ->setMultiple(true)
            ->setWhere(['u_tp_id'=>config('shl.distributor_type')])
            ->setLink('user');

        $inputController->bonus_lines('bonus_lines')->setLabel("Ratios");

        $inputController->setStructure([
            ['bns_name','bns_code'],
            ['bns_start_date','bns_end_date'],
            ['distributors'],
            ['bns_free_products','bns_products'],
            ['bonus_lines'],
            ['bns_excludes']
        ]); 
    }

    protected function setColumns(ColumnController $columnController){
        foreach($this->inputs->getOnlyPrivilegedInputs() as $name=>$input){
            if($input->getType()=='date'||$input->getType()=='ajax_dropdown')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input)
                    ->setSearchable(false);
            else if($input->getType()!='bonus_lines')
                $columnController->{$input->getType()}($name)
                    ->setLabel($input->getLabel())->setInput($input);
        }
        $columnController->custom('bonus_lines')
            ->setLabel('Ratios')
            ->setComponent('BonusLines')
            ->setSearchable(false)
            ->setRenderer(function($value){

                $formated = "";

                foreach ($value['lines'] as $key => $line) {
                    $formated .= "(Min:- ".$line['min'].','.PHP_EOL;
                    $formated .= "Max:- ".$line['max'].','.PHP_EOL;
                    $formated .= "Purchase:- ".$line['purchase'].','.PHP_EOL;
                    $formated .= "Free:- ".$line['free'].')'.PHP_EOL.PHP_EOL;
                }

                return $formated;
            });
        $columnController->date('created_at')->setLabel("Created Date");
    }

    public function beforeDelete($inst)
    {
        $inst->bns_end_date = date('Y-m-d');
        $inst->save();

        return false;
    }
}