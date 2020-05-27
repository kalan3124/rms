<?php
namespace App\Models;

use JsonSerializable;

/**
 * Itinerary date details
 * 
 * @method BataType getBataType()
 * @method float getMileage()
 * @method SubTown[] getSubTowns()
 * @method int getDateType() 2= Not Set ,  0 = Planned but no areas , 3 = Standard itinerary, 4 = Additional Route plan , 5 = Joint field worker , 7 = Changed itinerary
 * @method DayType[] getDayTypes()
 * @method boolean getWorkingDay()
 * @method boolean getFieldWorkingDay()
 * 
 * @method void setBataType( BataType $bataType=null)
 * @method void setMileage(float $mileage = 0.00)
 * @method void setSubTowns(array $subTowns = [])
 * @method void setDateType(int $dateType)
 * @method void setDayTypes(DayType[] $dayTypes)
 * @method void setWorkingDay(boolean $working = true)
 * @method void setFieldWorkingDay(boolean $fieldWorking = true)
 */
class ItineraryDateDetails extends SettersAndGetters{
    protected $bataType;

    protected $mileage=0.00;

    protected $subTowns=[];

    protected $dateType = 1;

    protected $dayTypes = [];

    protected $workingDay = true;

    protected $fieldWorkingDay = true;

    protected $properties =['bataType','mileage','subTowns','dateType','dayTypes','workingDay','fieldWorkingDay'];

}