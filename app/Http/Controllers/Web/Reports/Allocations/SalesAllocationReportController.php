<?php

namespace App\Http\Controllers\Web\Reports\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\SalesAllocation;
use App\Models\SalesAllocationMain;
use App\Models\TeamMemberPercentage;
use App\Models\TeamUser;
use App\Models\TmpSalesAllocation;
use App\Models\Town;
use \Illuminate\Http\Request;

class SalesAllocationReportController extends ReportController {

    protected $title = "Sales Allocation Report";

    public function search(Request $request)
    {
        $teamId = $request->input('values.team');
        $townId = $request->input('values.town');

        $salesAllocationQuery = SalesAllocationMain::query();

        $salesAllocationQuery->with('team');

        if(isset($teamId)){
            $salesAllocationQuery->where('tm_id',$teamId);
        }

        $count = $this->paginateAndCount($salesAllocationQuery,$request,'created_at');

        $salesAllocations = $salesAllocationQuery->get();

        $results = [];

        foreach ($salesAllocations as $allocationKey => $salesAllocation) {
            $towns = SalesAllocation::with('town')->where('sa_ref_type','1')->where('sam_id',$salesAllocation->sam_id)->get();

            $towns = $towns->map(function($allocatedTown){
                if(!$allocatedTown->town){
                    return null;
                }

                return [
                    'value'=>$allocatedTown->town->sub_twn_id,
                    'label'=>$allocatedTown->town->sub_twn_name,
                    'mode'=>$allocatedTown->sa_ref_mode==1?"include":"exclude"
                ];
            })
            ->filter(function($town){
                return !!$town;
            })
            ->values();

            if($towns->isEmpty()){
                $towns->push([
                    'value'=>0,
                    'label'=>"All",
                    'mode'=>'include'
                ]);
            }

            $chemists = SalesAllocation::with('chemist')->where('sa_ref_type','2')->where('sam_id',$salesAllocation->sam_id)->get();

            $chemists = $chemists->map(function($allocatedChemist){
                    if(!$allocatedChemist->chemist){
                        return null;
                    }

                    return [
                        'value'=>$allocatedChemist->chemist->chemist_id,
                        'label'=>$allocatedChemist->chemist->chemist_name,
                        'mode'=>$allocatedChemist->sa_ref_mode==1?"include":"exclude",
                        'sub_town'=>$allocatedChemist->chemist->sub_twn_id
                    ];
                })
                ->filter(function($chemist){
                    return !!$chemist;
                })
                ->values();
        
            if($chemists->isEmpty()){
                $chemists->push([
                    'value'=>0,
                    'label'=>"All",
                    'mode'=>'include',
                    'sub_town'=>0
                ]);
            }

            $products = SalesAllocation::with('product')->where('sa_ref_type',3)->where('sam_id',$salesAllocation->sam_id)->get();

            $products = $products->map(function($allocatedProduct){
                    if(!$allocatedProduct->product){
                        return null;
                    }

                    return [
                        'value'=>$allocatedProduct->product->product_id,
                        'label'=>$allocatedProduct->product->product_name,
                        'mode'=>$allocatedProduct->sa_ref_mode==1?"include":"exclude"
                    ];
                })
                ->filter(function($product){
                    return !!$product;
                })
                ->values();

            $teamMembers = collect([]);

            $team = null;
            
            if($salesAllocation->team){
                $team = $salesAllocation->team;

                $teamUsers = TeamUser::where('tm_id',$team->tm_id)->with('user')->get();

                $users = $teamUsers->map(function($teamUser)use($salesAllocation){
                    if(!$teamUser->user){
                        return null;
                    }

                    $percentage = TeamMemberPercentage::where('u_id',$teamUser->u_id)->where('sam_id',$salesAllocation->getKey())->first();


                    return [
                        'label'=>$teamUser->user->name,
                        'value'=>$teamUser->user->getKey(),
                        'percent'=>$percentage?$percentage->mp_percent."%":"0%"
                    ];
                })->filter(function($user){
                    return !!$user;
                })->values();

                $teamMembers  = $users;

            }

            $productsCount = $products->count();
            $teamMembersCount = $teamMembers->count();
            $chemistsCount = $chemists->count();

            $rowCount = max([$productsCount,$teamMembersCount,$chemistsCount]);
            
            $i=-1;
            foreach ($towns as $townKey => $town) {
                if($town['value']!=0)
                    $filteredChemists = $chemists->where('sub_town',$town['value'])->values();
                else
                    $filteredChemists = $chemists;
                
                foreach ($filteredChemists as $chemistKey=> $chemist){
                    ++$i;
                    
                    $row = [];

                    $row['team'] = ($i==0)?[
                        'label'=> $team->tm_name,
                        'value'=> $team->tm_id
                    ]: null;

                    $row['team_rowspan'] = ($i==0)?$rowCount:0;

                    $row['town'] = $chemistKey==0 ? $town: null;
                    $row['town_style'] = $chemistKey==0 ?[
                        'background'=>$town['mode']=="exclude"?'#FF8A65':"#64FFDA",
                    ]:null;

                    $row['town_rowspan'] = $chemistKey==0?$filteredChemists->count():0;

                    $row['customer']=  $chemist;
                    $row['customer_style'] = [
                        'background'=>$chemist['mode']=="exclude"?'#FF8A65':"#64FFDA",
                    ];

                    $product = $products->get($i);

                    $row['product'] = $product?$product:null;

                    $row['product_style'] = $product?[
                        'background'=>$product['mode']=="exclude"?'#FF8A65':"#64FFDA",
                    ]:null;

                    $agent = $teamMembers->get($i);

                    $row['agent'] = $agent;

                    $row['percentage'] = $agent?$agent['percent']:null;

                    $row['created_time'] = $salesAllocation->created_at->format("Y-m-d H:i:s");
                    $row['created_time_rowspan'] = ($i==0)?$rowCount:0;

                    $row['delete_button'] = $salesAllocation->sam_id;
                    $row['delete_button_rowspan'] = ($i==0)?$rowCount:0;
                    $results[] = $row;

                }

            }

            if($rowCount>$i+1){
                for ($j=$i+1; $j < $rowCount; $j++) { 
                    $row['team'] = null;
                    $row['team_rowspan'] = 0;

                    $row['town'] = null;
                    $row['town_rowspan'] = 1;
                    $row['town_style'] = [
                        'background'=>'#ffffff'
                    ];

                    $row['customer'] = null;
                    $row['customer_rowspan'] = 1;
                    $row['customer_style'] = [
                        'background'=>'#ffffff'
                    ];

                    $product = $products->get($j);

                    $row['product'] = $product?$product:null;


                    $row['product_style'] = $product?[
                        'background'=>$product['mode']=="exclude"?'#FF8A65':"#64FFDA",
                    ]:null;

                    $agent = $teamMembers->get($j);

                    $row['agent'] = $agent;

                    $row['percentage'] = $agent?$agent['percent']:null;

                    $row['created_time'] = $salesAllocation->created_at->format("Y-m-d H:i:s");
                    $row['created_time_rowspan'] = 0;

                    $row['delete_button'] = $salesAllocation->sam_id;
                    $row['delete_button_rowspan'] = 0;
                    $results[] = $row;

                }
            }


        }

        return [
            'count'=> $count,
            'results'=> $results
        ];
    }

    protected function setColumns(ColumnController $columnController,Request $request){

        $columnController->ajax_dropdown('team')->setLabel("Team");
        $columnController->ajax_dropdown('town')->setLabel("Town");
        $columnController->ajax_dropdown('customer')->setLabel("Customer");
        $columnController->ajax_dropdown('product')->setLabel("Product");
        $columnController->ajax_dropdown('agent')->setLabel("Agent");
        $columnController->text('percentage')->setLabel("Percentage");
        $columnController->text('created_time')->setLabel("Created Time");
        $columnController->button('delete_button')->setLabel("Delete")->setLink('report/sales_allocation/delete');
    }

    protected function setInputs($inputController){
       $inputController->ajax_dropdown("team")->setLabel("Team")->setLink("team")->setValidations('');

       $inputController->setStructure([
        ['team']
       ]);
    }

    public function delete(Request $request){
        $value = $request->input('value');

        SalesAllocationMain::where('sam_id',$value)->delete();

        SalesAllocation::where('sam_id',$value)->delete();

        TeamMemberPercentage::where('sam_id',$value)->delete();

        TmpSalesAllocation::where('sam_id',$value)->delete();

        return response()->json([
            'success'=>true,
            'message'=>"Successfully deleted the allocation."
        ]);
    }
}