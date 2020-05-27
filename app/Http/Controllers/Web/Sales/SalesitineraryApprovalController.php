<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use Illuminate\Http\JsonResponse;
use App\Models\Itinerary;
use App\Models\SalesItinerary;
use App\Models\User;
use App\Models\DistributorSalesRep;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesItineraryDate;
use App\Models\Chemist;
use App\Models\SfaSrItineraryDetail;
use App\Models\Area;

class SalesitineraryApprovalController extends Controller
{

    /**
     * Searching for approvals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {

        $user = Auth::user();

        // if($user->u_tp_id == config('shl.distributor_type')){
        //     $users->where('dis_id',$user->getKey());
        // }

        // $user = $request->input('user.value');
        $ar_id = $request->input('area.value');
        $type = $request->input('type', 0);
        $u_id = $request->input('user.value');
        $mode = $request->input('mode');

        if ($ar_id) {
            $area = Area::find($ar_id);
            $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();
        } else if ($u_id) {
            $users = User::where('id', $u_id)->get();
        } else {

            if ($user->id == config('shl.distributor_type')) {

                $dis = DistributorSalesRep::where('dis_id', $user->id)->get();
                $users = User::whereIn('id', $dis->pluck('dsr_id')->all())->get();
            } else {
                $users = User::orWhere('u_tp_id', $mode == "sales" ? config('shl.sales_rep_type') : config('shl.distributor_sales_rep_type'))
                    ->get();
            }
        }

        $itineraries = collect([]);

        foreach ($users as $user) {

            $query = SalesItinerary::query();

            $query->with('approver');

            $query->where(function ($query) use ($user) {
                $query->where('u_id', $user->id);
            });

            // if ($user->u_tp_id == config('shl.distributor_type')) {
            //     $query->where('dis_id', $user->getKey());
            // }

            $query->latest();

            $itinerary = $query->first();

            if ($itinerary) {
                $itinerary->user = $user;

                if (isset($itinerary->s_i_aprvd_at) && $type)
                    $itineraries->push($itinerary);
                else if (!isset($itinerary->s_i_aprvd_at) && !$type)
                    $itineraries->push($itinerary);
            }
        }

        $itineraries = $itineraries->filter(function ($itinerary) {
            return !!$itinerary;
        });

        $itineraries->transform(function ($itinerary) {
            $user_details = User::find($itinerary->u_id);
            return [
                'id' => $itinerary->getKey(),
                'type' => isset($itinerary->s_i_aprvd_at) ? 1 : 0,
                'approvedTime' => $itinerary->s_i_aprvd_at,
                'approvedBy' => $itinerary->approver ? $itinerary->approver->getName() : null,
                'yearMonth' => $itinerary->s_i_year . " - " . str_pad($itinerary->s_i_month, 2, '0', STR_PAD_LEFT),
                'createdTime' => $itinerary->created_at->format("Y-m-d H:i:s"),
                'user' => isset($user_details->name) ? $user_details->name : null

            ];
        });

        return response()->json([
            'results' => $itineraries
        ]);
    }
    /**
     * Approving an sales itinerary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function approve(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:sfa_itinerary,s_i_id'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException("Invalid request.");
        }

        $user = Auth::user();

        $itinerary = SalesItinerary::find($request->input('id'));

        $sfa_itinerary = SalesItineraryDate::where('s_i_id', $request->input('id'))->get();

        foreach ($sfa_itinerary as $key => $val) {

            if (isset($val->route_id)) {
                $chem_count = Chemist::where('route_id', $val->route_id)->count('chemist_id');
            }

            SfaSrItineraryDetail::create([
                'sr_i_year' => $itinerary->s_i_year,
                'sr_i_month' => $itinerary->s_i_month,
                'sr_i_date' => $val->s_id_date,
                'sr_id' => $itinerary->u_id,
                'route_id' => $val->route_id,
                'outlet_count' => isset($chem_count) ? $chem_count : 0,
                'sr_mileage' => $val->s_id_mileage,
                'bt_id' => $val->bt_id
            ]);
        }

        // $this->saveSrItineraryDetails($request->input('id'),$itinerary->s_i_year,$itinerary->s_i_month,$itinerary->u_id);

        $itinerary->s_i_aprvd_at = date("Y-m-d H:i:s");

        $itinerary->s_aprvd_u_id = $user->getKey();

        $itinerary->save();

        return response()->json([
            'success' => true,
            'message' => "You have successfully approved the itinerary!"
        ]);
    }

    public function saveSrItineraryDetails($sr_i_id, $year, $month, $u_id)
    {
        $sfa_itinerary = SalesItineraryDate::where('s_i_id', $sr_i_id)->get();

        foreach ($sfa_itinerary as $key => $val) {
            if (isset($val->route_id)) {
                $chem_count = Chemist::where('route_id', $val->route_id)->count('chemist_id');
            }

            SfaSrItineraryDetail::create([
                'sr_i_year' => $year,
                'sr_i_month' => $month,
                'sr_i_date' => $val->s_id_date,
                'sr_id' => $u_id,
                'route_id' => $val->route_id,
                'outlet_count' => isset($chem_count) ? $chem_count : 0,
                'sr_mileage' => $val->s_id_mileage,
                'bt_id' => $val->bt_id
            ]);
        }
        return $sfa_itinerary;
    }
}
