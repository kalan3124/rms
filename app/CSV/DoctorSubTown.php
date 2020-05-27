<?php
namespace App\CSV;

use App\Models\DoctorSubTown as DoctorSubTownModel;
use App\Models\Doctor;
use App\Models\SubTown;

class DoctorSubTown extends Allocation{
    protected $title = "Doctor Sub Towns";

    protected $columns = [
        'doc_id'=>"Doctor code",
        'sub_twn_id'=>"Sub Town Code"
    ];

    protected $allocationsClass=DoctorSubTownModel::class;

    protected $mainClass = Doctor::class;

    protected $childClasses = [
        'sub_twn_id'=>SubTown::class,
    ];

}