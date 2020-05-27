<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Exceptions\MediAPIException;
use App\Exceptions\WebAPIException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * User Model
 *
 * @property int $id Auto increment id
 * @property string $name
 * @property string $email
 * @property int $u_tp_id User type id
 * @property float $base_allowances
 * @property float $private_mileage
 * @property string $contact_no
 * @property string $user_name
 * @property int $divi_id
 * @property int $price_list
 * @property string $u_code
 * @property string $report_access_token
 * @property int $vht_id Vehicle Type
 * @property string $u_base_lov Base in string
 * @property float $u_pvt_mileage_limit
 * @property float $u_prking_limit
 * @property float $u_ad_mileage_limit
 *
 * @property UserType $user_type
 * @property Division $division
 * @property VehicleType $vehicle_type
 * @property TeamUser $teamUser
 * @property Team $fmTeam
 * @property TeamMemberPercentage $salesPercentage
 */
class User extends Authenticatable
{
    use Notifiable,HasApiTokens,SoftDeletes,RevisionableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','u_tp_id','base_allowances','private_mileage','contact_no','user_name','divi_id','price_list','u_code','report_access_token','vht_id','u_base_lov','u_pvt_mileage_limit','u_prking_limit','u_ad_mileage_limit','u_password_created','day_mileage_limit','fail_attempt'
    ];

    protected $codeName = 'u_code';

    /**
     * Returning the code name
     *
     * @return string
     */
    public function getCodeName(){
        return $this->codeName;
    }
    /**
     * Returning the code
     *
     * @return mixed
     */
    public function getCode(){
        $codeName = $this->getCodeName();

        return $this->{$codeName};
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getRoll(){
        return $this->u_tp_id;
    }

    public function getName(){
        return $this->name;
    }

    public function getRollName(){
        return $this->user_type->u_tp_name;
    }

    public function getProfilePicture(){
        return null;
    }

    public function getBase64ProfilePicture(){

        $imageName = $this->getProfilePicture();

        if(!$imageName) return null;

        $image = Storage::disk('local')->exists('public/images/users/512x512/'.$imageName.'.jpg');

        return base64_encode($image);
    }

    public function getPhoneNumber(){
        return '07190323244';
    }

    public function getEmail(){
        return $this->email;
    }

    public function user_type (){
        return $this->belongsTo(UserType::class,'u_tp_id','u_tp_id');
    }

    public function division(){
        return $this->belongsTo(Division::class,'divi_id','divi_id');
    }

    public function vehicle_type(){
        return $this->belongsTo(VehicleType::class,'vht_id','vht_id');
    }

    public function teamUser(){
        return $this->hasOne(TeamUser::class,'u_id','id');
    }

    public function fmTeam(){
        return $this->hasOne(Team::class,'fm_id','id');
    }

    public function checkItinerary($approved=true,$workingDayCheck=true){

        $itineraryDate = ItineraryDate::getTodayForUser($this,['itineraryDayTypes','itineraryDayTypes.dayType'],null,false,$approved);

        if($workingDayCheck){

            $isWorkingDate = false;

            foreach($itineraryDate->itineraryDayTypes as $itineraryDayType){
                if($itineraryDayType->dayType->dt_is_working) $isWorkingDate=true;
            }

            if(!$isWorkingDate) throw new MediAPIException("Today is not a working day.",13);
        }

        return true;
    }

    public function checkSfaItinerary($approved=true,$workingDayCheck=true){

        $itineraryDate = SalesItineraryDate::getTodayForUser($this,['salesItineraryDateDayTypes','salesItineraryDateDayTypes.dayType'],null,false,$approved);

        if($workingDayCheck){

            $isWorkingDate = false;

            foreach($itineraryDate->salesItineraryDateDayTypes as $itineraryDayType){
                if($itineraryDayType->dayType->dt_is_working) $isWorkingDate=true;
            }

            if(!$isWorkingDate) throw new MediAPIException("Today is not a working day.",13);
        }

        return true;
    }

    public static function getByUser($user){

        if($user->getRoll()==config('shl.field_manager_type')){
            $teams = Team::where('fm_id',$user->getKey())->with(['teamUsers','teamUsers.user'])->latest()->first();

            if($teams){
                $teams->teamUsers->transform(function($teamUser){
                    return $teamUser->user;
                });

                $users = $teams->teamUsers->filter(function($fltUser){return !!$fltUser;});

            } else {
                $users = collect([]);
            }
            $users->push($user);

            return $users;

        } else if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            return collect([$user]);
        } else
            return collect([$user]);
    }



    public function salesPercentage(){
        return $this->hasOne(TeamMemberPercentage::class,'id','u_id');
    }
}
