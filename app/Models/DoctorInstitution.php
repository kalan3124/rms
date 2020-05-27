<?php

namespace App\Models;

class DoctorInstitution extends Base
{
    protected $table = 'doctor_intitution';

    protected $primaryKey = 'doci_id';

    protected $fillable = [
        'doc_id', 'ins_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doc_id', 'doc_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'ins_id', 'ins_id');
    }
}
