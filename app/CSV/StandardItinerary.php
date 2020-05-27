<?php
namespace App\CSV;

use \App\Models\User;
use App\Exceptions\WebAPIException;
use App\Models\BataType;
use App\Models\Chemist;
use App\Models\Doctor;
use App\Models\OtherHospitalStaff;
use App\Models\SubTown;
use App\Models\StandardItinerary as StandardItineraryModel;
use App\Models\StandardItineraryDate;
use App\Models\StandardItineraryDateArea;
use App\Models\StandardItineraryDateCustomer;
use Illuminate\Support\Facades\Storage;

class StandardItinerary extends Base{
    protected $title = "Standard Itinerary";

    protected $currentUser = null;

    protected $currentDate = null;

    protected $currentSubtown = null;

    protected $tips = [
        "Please sort the attached CSV file by 'Employee Code', 'Day Number', 'Sub Town Code' before upload."
    ];

    protected $columns = [
        'userId'=>"Employee code",
        'dayNumber'=>"Day Number",
        'description'=>"Standard Itinerary Description",
        'bata'=>"Bata Code",
        'mileage'=>"Mileage",
        'subTownId'=>"Sub Town Code",
        'chemistId'=>"Chemist Code",
        'doctorId'=>"Doctor Code",
        'otherHospitalStaffId'=>"Other Hospital Staff Code"
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'dayNumber':
                if(!$value)
                    throw new WebAPIException("Day number is required!");
                return $value;
            case 'userId':
                if(!$value)
                    throw new WebAPIException("Employee Code is required!");
                $userCodeName = (new User)->getCodeName();
                $user = User::where($userCodeName,$value)->first();
                if(!$user)
                    throw new WebAPIException("Code mismatching issue for user. Given code is $value");
                $this->currentUser = $user;
                return $user->getKey();
            case 'description':
                if(strlen($value)<3)
                    throw new WebAPIException("Enter valid standard itinerary description.");
                return $value;
            case 'bata':
                if($value){
                    $bataCodeName = (new BataType)->getCodeName();
                    $bata = BataType::where($bataCodeName,'LIKE',$value)->where('divi_id', $this->currentUser? $this->currentUser->divi_id:0)->first();
                    if(!$bata)
                        throw new WebAPIException("Can not find a bata type for the given code. Given code is ".$value);
                    return $bata->getKey();
                }
                return null;
            case 'mileage':
                if(!$value||!is_numeric($value))
                    throw new WebAPIException("Please enter a valid mileage value. Entered value is ".$value);
                return $value;
            case 'subTownId':
                if(!$value)
                    throw new WebAPIException("Sub town code is required");

                $subTown = SubTown::where('sub_twn_code',$value)->first();

                if(!$subTown)
                    throw new WebAPIException("Can not find a sub town for the given code. Given code is ". $value);

                return $subTown->getKey();
            case 'chemistId':
                if(!$value)
                    return null;

                $chemist = Chemist::where('chemist_code',$value)->first();

                if(!$chemist)
                    throw new WebAPIException("Can not find a chemist for the given code. Given code is ".$value);

                return $chemist->getKey();
            case 'doctorId':
                if(!$value)
                    return null;

                $doctor = Doctor::where('doc_code',$value)->first();

                if(!$doctor)
                    throw new WebAPIException("Can not find a doctor for the given code. Given code is ".$value);

                return $doctor->getKey();
            case 'otherHospitalStaffId':
                if(!$value)
                    return null;

                $otherHospitalStaff = OtherHospitalStaff::where('hos_stf_code',$value)->first();

                if(!$otherHospitalStaff)
                    throw new WebAPIException("Can not find a other hospital staff for the given code. Given code is ".$value);

                return $otherHospitalStaff->getKey();
            default:
                return $value;
        }
    }

    protected function insertRow($row)
    {
        if(empty($this->previousRow)||!isset($this->previousRow['userId'])||$this->previousRow['userId']!=$row['userId']){
            $standardItinerary = StandardItineraryModel::create([
                'u_id'=>$row['userId']
            ]);
        } else {
            $standardItinerary = StandardItineraryModel::where('u_id',$row['userId'])->latest()->first();
        }

        if(!$standardItinerary) throw new WebAPIException("Something went wrong!");

        if( empty($this->previousRow) ||
            !isset($this->previousRow['userId']) ||
            $this->previousRow['userId']!=$row['userId'] ||
            !isset($this->previousRow['dayNumber']) ||
            $this->previousRow['dayNumber']!=$row['dayNumber']
        ){
            $standardItineraryDate = StandardItineraryDate::create([
                'si_id'=>$standardItinerary->getKey(),
                'bt_id'=>$row['bata'],
                'sid_mileage'=>$row['mileage'],
                'sid_description'=>$row['description'],
                'sid_date'=>$row['dayNumber']
            ]);
        } else {
            $standardItineraryDate = StandardItineraryDate::where('si_id',$standardItinerary->getKey())->where('sid_date',$row['dayNumber'])->latest()->first();
        }

        if(!$standardItineraryDate) throw new WebAPIException("Something went wrong!");

        if( empty($this->previousRow) ||
            !isset($this->previousRow['userId']) ||
            $this->previousRow['userId']!=$row['userId'] ||
            !isset($this->previousRow['dayNumber']) ||
            $this->previousRow['dayNumber']!=$row['dayNumber'] ||
            !isset($this->previousRow['subTownId']) ||
            $this->previousRow['subTownId']!=$row['subTownId']
        ){
            StandardItineraryDateArea::firstOrCreate([
                'sid_id'=>$standardItineraryDate->getKey(),
                'sub_twn_id'=>$row['subTownId']
            ]);
        }

        if($row['chemistId'])
            StandardItineraryDateCustomer::create([
                'sid_id'=>$standardItineraryDate->getKey(),
                'chemist_id'=>$row['chemistId']
            ]);

        if($row['doctorId'])
            StandardItineraryDateCustomer::create([
                'sid_id'=>$standardItineraryDate->getKey(),
                'doc_id'=>$row['doctorId']
            ]);

        if($row['otherHospitalStaffId'])
            StandardItineraryDateCustomer::create([
                'sid_id'=>$standardItineraryDate->getKey(),
                'hos_stf_id'=>$row['otherHospitalStaffId']
            ]);
    }

}
