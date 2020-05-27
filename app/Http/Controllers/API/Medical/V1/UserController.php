<?php

namespace App\Http\Controllers\API\Medical\V1;

use App\Exceptions\MediAPIException;
use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\DoctorSubTown;
use App\Models\Expenses;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\Notification;
use App\Models\OtherHospitalStaff as ModelsOtherHospitalStaff;
use App\Models\StationMileage;
use App\Models\UnproductiveVisit;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\VehicleTypeRate;
use App\Traits\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;
use \Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use Territory;
    /**
     * Login a user
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), ['username' => 'required|exists:users,user_name', 'password' => 'required']);

        if ($validator->fails()) {
            // Error ME1
            throw new MediAPIException("User not found!", 1);
        }

        if (Auth::attempt(['user_name' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();

            if (!in_array($user->getRoll(), config('shl.medical_app_privileged_user_types'))) {
                // Error ME2
                throw new MediAPIException("User Restricted!", 2);
            } else {

                $ItineraryApprovedStatus = false;
                $hasItinerary = false;

                try {
                    $user->checkItinerary(true, false);
                    $ItineraryApprovedStatus = true;
                } catch (\Exception $e) {
                    $ItineraryApprovedStatus = false;
                }

                try {
                    $user->checkItinerary(false, false);
                    $hasItinerary = true;
                } catch (\Exception $e) {
                    $hasItinerary = false;
                }

                if ($user->updated_at->format("Y-m-d") != date("Y-m-d")) {
                    $reportHash = preg_replace('/[^a-zA-Z0-9]/i', "", strtolower(base64_encode(microtime() . "_" . $user->getKey())));

                    $user->report_access_token = $reportHash;
                    $user->save();
                }

                $exp_addtional_amount = Expenses::where('u_id', $user->getKey())
                    ->where('rsn_id', 14)
                    ->whereDate('exp_date', date('Y-m-d'))
                    ->sum('exp_amt');

                $exp_parking_amount = Expenses::where('u_id', $user->getKey())
                    ->where('rsn_id', 15)
                    ->whereDate('exp_date', date('Y-m-d'))
                    ->sum('exp_amt');

                $year = date('Y');
                $month = date('m');

                $totalExpensesCurrentMonth = $this->getClaimedExpensesForMonth($year, $month, $user);

                $totalExpensesLastMonth = $this->getClaimedExpensesForMonth($year, --$month, $user);

                $lastAttendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();

                if (isset($lastAttendance) && isset($lastAttendance->check_out_time)) {
                    $missCalls = $this->missCallAlert($user, $lastAttendance);
                }

                if (isset($missCalls)) {
                    foreach ($missCalls as $key => $miss) {
                        foreach ($miss['missedVisits'] as $key => $value) {
                            Notification::create([
                                'n_title' => "Previous Miss Vists on " . date('Y-m-d', strtotime($lastAttendance->check_in_time)),
                                'n_content' => "<b>" . $user->name .
                                "</b> Previous Miss Vists on <b>" . date("Y-m-d", strtotime($lastAttendance->check_in_time)) .
                                "</b>.<br/> Chemist Name:- <b>" . $value['doc_chem_name'] .
                                "</b>.<br/> Speciality:- <br>" . $value['speciality'] . "</br>",
                                'u_id' => $user->getKey(),
                                'n_created_u_id' => $user->getKey(),
                                'n_type' => 1,
                                'n_ref_id' => $lastAttendance->getKey(),
                            ]);
                        }
                    }
                }

                return response()->json([
                    'result' => true,
                    'token' => $user->createToken('MediApp')->accessToken,
                    'name' => $user->getName(),
                    'picture' => $user->getBase64ProfilePicture(),
                    'phoneNumber' => $user->getPhoneNumber(),
                    'email' => $user->getEmail(),
                    'reportAccess' => $user->report_access_token,
                    'addtional_mileage_limit' => isset($user->u_ad_mileage_limit) ? $user->u_ad_mileage_limit - $exp_addtional_amount : 0,
                    'parking_mileage_limit' => isset($user->u_prking_limit) ? $user->u_prking_limit - $exp_parking_amount : 0,
                    "Itinerary_Approved_Status" => $ItineraryApprovedStatus,
                    "hasItinerary" => $hasItinerary,
                    'totalExpensesLastMonth' => $totalExpensesLastMonth,
                    'totalExpensesCurrentMonth' => $totalExpensesCurrentMonth,
                    'userType' => $user->divi_id,
                ]);
            }

        } else {
            // Error ME3
            throw new MediAPIException("Password incorrect!", 3);
        }
    }

    protected function getClaimedExpensesForMonth($year, $month, $user)
    {

        $total = 0;

        $itinerary = Itinerary::where(function ($query) use ($user) {
            $query->orWhere('rep_id', $user ? $user->id : 0);
            $query->orWhere('fm_id', $user ? $user->id : 0);
        })
            ->whereNotNull('i_aprvd_at')
            ->where('i_year', $year)
            ->where('i_month', $month)
            ->latest()
            ->first();

        if (!$itinerary) {
            return $total;
        }

        $itineraryRelations = [
            'joinFieldWorker',
            'itineraryDayTypes',
            'itineraryDayTypes.dayType',
            'standardItineraryDate',
            'standardItineraryDate.bataType',
            'additionalRoutePlan',
            'additionalRoutePlan.bataType',
            'changedItineraryDate',
            'changedItineraryDate.bataType',
            'bataType',
        ];

        $startDate = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-01';
        $endDate = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . date('t');

        $itineraryDates = ItineraryDate::with($itineraryRelations)
            ->where('i_id', $itinerary->getKey())
            ->get();

        $itineraryDates->transform(function (ItineraryDate $itineraryDate) use ($itinerary, $user, $startDate, $endDate) {
            $mileage = 0;
            $bataValue = 0;

            $date = $itinerary->i_year . "-" . str_pad($itinerary->i_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($itineraryDate->id_date, 2, "0", STR_PAD_LEFT);

            $vehicleTypeRateInst = VehicleTypeRate::where('vht_id', $user->vht_id)->whereDate('vhtr_srt_date', '<=', $date)->where('u_tp_id', $user->u_tp_id)->latest()->first();
            $vehicleTypeRate = $vehicleTypeRateInst ? $vehicleTypeRateInst->vhtr_rate : 0;

            $attendanceStatus = UserAttendance::where('u_id', '=', $user->id)->whereDate('check_in_time', '=', $date)->whereNotNull('check_out_time')->latest()->first();

            $stationMileage = StationMileage::where('u_id', $user->id)->whereDate('exp_date', $date)->first();

            $backDatedExpences = Expenses::where('u_id', $user->id)->whereDate('exp_date', $date)->first();

            $details = $itineraryDate->getFormatedDetails();
            $bataType = $details->getBataType();
            $bataValue = $bataType ? $bataType->bt_value : 0;
            $mileage = $details->getMileage() * $vehicleTypeRate;

            if (strtotime($startDate) <= strtotime($date) && strtotime($endDate) >= strtotime($date) && strtotime($date) <= time() && ($attendanceStatus || $backDatedExpences || $stationMileage || (!$details->getFieldWorkingDay() && $details->getWorkingDay()))) {
                return [
                    "mileageValue" => $mileage,
                    "bataValue" => $bataValue,
                    'vehicleTypeRate' => $vehicleTypeRate,
                ];
            }

            return null;
        });

        $itineraryDates = $itineraryDates->filter(function ($itineraryDate) {
            return !!$itineraryDate;
        })->values();

        $lastDate = $itineraryDates->last();
        $vehicleTypeRate = $lastDate ? $lastDate['vehicleTypeRate'] : 0;

        $total += $itineraryDates->sum('bataValue');
        $total += $user->base_allowances;
        $total += ($user->u_pvt_mileage_limit ? $user->u_pvt_mileage_limit : 0) * $vehicleTypeRate;
        $total += $itineraryDates->sum('mileageValue');

        $total += Expenses::whereDate('exp_date', ">=", $startDate)
            ->whereDate('exp_date', '<=', $endDate)
            ->where('u_id', $user->id)
            ->sum('exp_amt') * $vehicleTypeRate;

        return $total;
    }

    public function userDetails(Request $request)
    {

        $user = Auth::user();

        if (!in_array($user->getRoll(), config('shl.medical_app_privileged_user_types'))) {
            // Error ME2
            throw new MediAPIException("User Restricted!", 2);
        } else {

            //  $hasItinerary = false;
            $ItineraryApprovedStatus = false;
            $hasItinerary = false;

            try {
                $user->checkItinerary(true, false);
                $ItineraryApprovedStatus = true;
            } catch (\Exception $e) {
                $ItineraryApprovedStatus = false;
            }

            try {
                $user->checkItinerary(false, false);
                $hasItinerary = true;
            } catch (\Exception $e) {
                $hasItinerary = false;
            }

            if ($user->updated_at->format("Y-m-d") != date("Y-m-d")) {
                $reportHash = preg_replace('/[^a-zA-Z0-9]/i', "", strtolower(base64_encode(microtime() . "_" . $user->getKey())));

                $user->report_access_token = $reportHash;
                $user->save();
            }

            $exp_addtional_amount = Expenses::where('u_id', $user->getKey())
                ->where('rsn_id', 14)
                ->whereDate('exp_date', date('Y-m-d'))
                ->sum('exp_amt');

            $exp_parking_amount = Expenses::where('u_id', $user->getKey())
                ->where('rsn_id', 15)
                ->whereDate('exp_date', date('Y-m-d'))
                ->sum('exp_amt');

            $year = date('Y');
            $month = date('m');

            $totalExpensesCurrentMonth = $this->getClaimedExpensesForMonth($year, $month, $user);

            $totalExpensesLastMonth = $this->getClaimedExpensesForMonth($year, --$month, $user);

            return response()->json([
                'result' => true,
                'token' => $user->createToken('MediApp')->accessToken,
                'name' => $user->getName(),
                'picture' => $user->getBase64ProfilePicture(),
                'phoneNumber' => $user->getPhoneNumber(),
                'email' => $user->getEmail(),
                'reportAccess' => $user->report_access_token,
                'addtional_mileage_limit' => isset($user->u_ad_mileage_limit) ? $user->u_ad_mileage_limit - $exp_addtional_amount : 0,
                'parking_mileage_limit' => isset($user->u_prking_limit) ? $user->u_prking_limit - $exp_parking_amount : 0,
                "Itinerary_Approved_Status" => $ItineraryApprovedStatus,
                "hasItinerary" => $hasItinerary,
                'totalExpensesLastMonth' => $totalExpensesLastMonth,
                'totalExpensesCurrentMonth' => $totalExpensesCurrentMonth,
            ]);
        }

    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), ['current_pw' => 'required', 'new_password' => 'required|min:6']);

        if ($validator->fails()) {
            throw new MediAPIException($validator->errors()->first(), 4);
        }
        $user = Auth::user();

        $rep = User::where('id', $user->getKey())->latest()->first();
        //check pw with stored one
        if ($rep->user_name && Hash::check($request->current_pw, $rep->password)) {

            $user->password = Hash::make($request->new_password);
            $user->save();

            return [
                'result' => true,
                "message" => "New password has been successfully updated",
            ];
        } else {
            throw new MediAPIException("Current password is not matching. please provide correct password ", 14);
        }

    }

    public function missCallAlert($user, $lastAttendance)
    {

        try {
            $itineraryTowns = $this->getTerritoriesByItinerary($user, strtotime($lastAttendance->check_in_time));
        } catch (\Throwable $exception) {
            $itineraryTowns = collect();
        }

        $itinerarySubTownIds = [];

        if ($itineraryTowns->isEmpty()) {
            $itinerarySubTownIds = [];
        } else {
            $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');
        }

        // Getting chemists for above time ids
        $chemists = Chemist::with('sub_town')->whereIn('sub_twn_id', $itinerarySubTownIds)->get();

        $unproductiveVisit = UnproductiveVisit::with('chemist', 'chemist.sub_town')
            ->whereDate('unpro_time', '=', $lastAttendance->check_in_time)
            ->where('u_id', $user->getKey())
            ->get();

        $missedChemist = $unproductiveVisit->whereIn('chemist_id', $chemists->pluck('chemist_id')->all());

        $missedChemist->transform(function ($msChem) {
            return [
                'doc_chem_id' => $msChem->chemist_id,
                'doc_chem_name' => $msChem->chemist->chemist_name,
                'doc_chem_type' => 1, //Chemist
                'speciality' => $msChem->chemist->sub_town->sub_twn_name,
            ];
        });

        // Getting doctors for today
        $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i', 'i.ins_id', '=', 'ti.ins_id', 'inner')
            ->whereIn('i.sub_twn_id', $itinerarySubTownIds)
            ->where([
                'i.deleted_at' => null,
                'ti.deleted_at' => null,
            ])
            ->select('ti.doc_id')
            ->groupBy('ti.doc_id')
            ->get();
        //getting doctors from subtown assignment
        $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id', $itinerarySubTownIds)->get();
        //merge subtown doctors with institute assigned doctors
        $doctors = $doctors->merge($doctorsBySubTown);

        //get missed doctors
        $missedDoctors = $unproductiveVisit->whereIn('doc_id', $doctors->pluck('doc_id')->all());

        $missedDoctors->transform(function ($msDoc) {
            return [
                'doc_chem_id' => $msDoc->doc_id,
                'doc_chem_name' => $msDoc->doctor->doc_name,
                'doc_chem_type' => 0, //Doctor,
                'speciality' => 'speciality : ' . $msDoc->doctor->doctor_speciality->speciality_name,
            ];
        });

        // Getting Other Hospital Staff
        $otherStaff = ModelsOtherHospitalStaff::whereIn('sub_twn_id', $itinerarySubTownIds)->get();
        //get other hospital staff
        $missedOtherHos = $unproductiveVisit->whereIn('hos_stf_id', $otherStaff->pluck('hos_stf_id')->all());

        $missedOtherHos->transform(function ($msHos) {
            return [
                'doc_chem_id' => $msHos->hos_stf_id,
                'doc_chem_name' => $msHos->other_hos_staff->hos_stf_name,
                'doc_chem_type' => 2, //Other Hos Staff
                'speciality' => '',
            ];
        });

        $filturedDocChemHos = $missedChemist->concat($missedDoctors)->concat($missedOtherHos);
        $filturedDocChemHos->all();

        if (!$filturedDocChemHos->isEmpty()) {
            $result[] = [
                "date" => $lastAttendance->check_in_time,
                "missedVisits" => $filturedDocChemHos,
            ];
        }

        return isset($result) ? $result : null;
    }
}
