<?php

namespace App\Traits;

use App\Models\Chemist;
use App\Models\InvoiceAllocation;
use App\Models\Itinerary as ItineraryModel;
use App\Models\ItineraryDate;
use App\Models\SalesAllocation;
use App\Models\SalesAllocationMain;
use App\Models\Team;
use App\Models\TeamMemberPercentage;
use App\Models\TeamUser;
use Illuminate\Support\Facades\DB;
use App\Models\User;

trait Territory
{
    protected $territoryColumns = [
        't.twn_id',
        't.twn_name',
        't.twn_code',
        'st.sub_twn_id',
        'st.sub_twn_name',
        'st.sub_twn_code',
        'a.ar_id',
        'a.ar_name',
        'a.ar_code',
        'r.rg_id',
        'r.rg_name',
        'r.rg_code'
    ];
    /**
     * Returning the today allocated areas by itinerary for a user
     *
     * You can pluck sub town ids, area ids ,... from this collection
     *
     * @param \App\Models\User $user
     * @param int $day unix timestamp of the day
     * @param bool $approved only take approved
     * @return \Illuminate\Support\Collection
     */
    public function getTerritoriesByItinerary($user,$day=null,$approved=true)
    {

        $itineraryDate = ItineraryDate::getTodayForUser($user,['additionalRoutePlan','changedItineraryDate'],$day,false,$approved);

        $standardItineraryDateId = $itineraryDate->sid_id;
        $additionalRoutePlanId = 0;
        $changedPlanId =0;
        if(isset($itineraryDate->additionalRoutePlan)){
            $additionalRoutePlanId = $itineraryDate->additionalRoutePlan->getKey();
        }

        if(isset($itineraryDate->changedItineraryDate)&&isset($itineraryDate->changedItineraryDate->idc_aprvd_at)){
            $changedPlanId = $itineraryDate->changedItineraryDate->getKey();
            $additionalRoutePlanId = 0;
            $standardItineraryDateId =0;
        }

        // Retrieving town ids related to the above itinerary date
        $itineraryTowns = $this->__getSubtownsByItineraryIds([$standardItineraryDateId],[$additionalRoutePlanId],[$changedPlanId]);

        if($itineraryTowns->isEmpty()){
            $this->territoryColumns = array_filter($this->territoryColumns,function($column){
                if($column=='uist.sid_id'||$column=='uist.arp_id'||$column=='uist.idc_id')
                    return false;
                return true;
            });

            return $this->getAllocatedTerritories($user);
        }

        return $itineraryTowns;
    }

    protected function __getSubtownsByItineraryIds($standardItineraryIds,$routePlanIds=[],$changedPlanIds=[],$groupBy='st.sub_twn_id'){
        $this->territoryColumns[] = 'uist.sid_id';
        $this->territoryColumns[] = 'uist.arp_id';
        $this->territoryColumns[] = 'uist.idc_id';

        return  DB::table('sub_town AS st')->where([
            'a.deleted_at' => null,
            't.deleted_at' => null,
            'r.deleted_at' => null,
            'st.deleted_at' => null,
            'uist.deleted_at'=>null
        ])
        ->where(function($query)use($standardItineraryIds,$routePlanIds,$changedPlanIds){
            $query->orWhereIn('uist.sid_id',$standardItineraryIds);
            $query->orWhereIn('uist.arp_id',$routePlanIds);
            $query->orWhereIn('uist.idc_id',$changedPlanIds);
        })
        ->join('town AS t', 't.twn_id', '=', 'st.twn_id', 'inner')
        ->join('area AS a', 'a.ar_id', '=', 't.ar_id', 'inner')
        ->join('region AS r', 'r.rg_id', '=', 'a.rg_id', 'inner')
        ->join('tmp_user_itinerary_sub_town AS uist','uist.sub_twn_id','st.sub_twn_id')
        ->groupBy($groupBy)
        ->select($this->territoryColumns)
        ->get();
    }
    /**
     * Returning all allocated areas for a user
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Support\Collection
     */
    public function getAllocatedTerritories($user)
    {
        $users = User::getByUser($user);

        $allocationColumns = [
            DB::raw('ua.sub_twn_id AS ua_sub_twn_id'),
            DB::raw('ua.twn_id AS ua_twn_id'),
            DB::raw('ua.ar_id AS ua_ar_id'),
            DB::raw('ua.rg_id AS ua_rg_id')
        ];

        // Retrieving town ids related to the above itinerary date
        $allocatedTerritories = DB::table('sub_town AS st')->where([
                'ua.deleted_at' => null,
                'a.deleted_at' => null,
                't.deleted_at' => null,
                'r.deleted_at' => null,
                'st.deleted_at' => null,
            ])
            ->whereIn('ua.u_id',$users->pluck('id')->all())
            ->join('town AS t', 't.twn_id', '=', 'st.twn_id', 'inner')
            ->join('area AS a', 'a.ar_id', '=', 't.ar_id', 'inner')
            ->join('region AS r', 'r.rg_id', '=', 'a.rg_id', 'inner')
            ->leftJoin('user_areas AS ua', function ($join) {
                $join->orOn('ua.sub_twn_id', '=', 'st.sub_twn_id');
                $join->orOn('ua.twn_id', '=', 't.twn_id');
                $join->orOn('ua.ar_id', '=', 'a.ar_id');
                $join->orOn('ua.rg_id', '=', 'r.rg_id');
            })
            ->groupBy('st.sub_twn_id')
            ->select(array_merge($this->territoryColumns,$allocationColumns))
            ->get();

        return $allocatedTerritories;
    }

    public function getAllocatedTerritoriesForSales($user){
        $allocatedTerritories = $this->getAllocatedTerritories($user);
        $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

        if($teamUser){

                $teamMemberPercentage = TeamMemberPercentage::where('u_id',$user->getKey())->where('mp_percent','>',0)->get();

                $salesAllocationChemists = SalesAllocation::where('sa_ref_type',2)->whereIn('sam_id',$teamMemberPercentage->pluck('sam_id'))->get();

                $chemists = Chemist::whereIn('chemist_id',$salesAllocationChemists->pluck('sa_ref_id'))->get();

                $invoiceAllocationTowns = InvoiceAllocation::with([
                    'invoiceLine',
                    'invoiceLine.chemist',
                    'invoiceLine.chemist.sub_town',
                    'returnLine',
                    'returnLine.chemist',
                    'returnLine.chemist.sub_town',
                ])->where('tm_id',$teamUser->tm_id)->get();

                $subTownIds = $invoiceAllocationTowns->pluck('invoiceLine.chemist.sub_town.sub_twn_id')->all();

                $returnSubTownIds = $invoiceAllocationTowns->pluck('returnLine.chemist.sub_town.sub_twn_id')->all();

                // Retrieving town ids related to the above itinerary date
                $salesAllocatedTerritories = DB::table('sub_town AS st')->where([
                        'a.deleted_at' => null,
                        't.deleted_at' => null,
                        'r.deleted_at' => null,
                        'st.deleted_at' => null,
                    ])
                    ->whereIn('st.sub_twn_id',array_merge($chemists->pluck('sub_twn_id')->all(),$subTownIds,$returnSubTownIds))
                    ->join('town AS t', 't.twn_id', '=', 'st.twn_id', 'inner')
                    ->join('area AS a', 'a.ar_id', '=', 't.ar_id', 'inner')
                    ->join('region AS r', 'r.rg_id', '=', 'a.rg_id', 'inner')
                    ->groupBy('st.sub_twn_id')
                    ->select($this->territoryColumns)
                    ->get();

                $allocatedTerritories = $allocatedTerritories->concat($salesAllocatedTerritories);
        }
        $allocatedTerritories = $allocatedTerritories->unique('sub_twn_id');

        $allocatedTerritories = $allocatedTerritories->filter(function($subTown){
            return !!$subTown->sub_twn_id ;
        });

        return $allocatedTerritories;
    }


}
