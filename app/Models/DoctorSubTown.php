<?php

namespace App\Models;

class DoctorSubTown extends Base
{
    protected $table = 'doctor_sub_towns';

    protected $primaryKey = 'dst_id';

    protected $fillable = [
        'doc_id', 'sub_twn_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doc_id', 'doc_id');
    }

    public function subTown()
    {
        return $this->belongsTo(SubTown::class, 'sub_twn_id', 'sub_twn_id');
    }

    public function DoctorInstitution()
    {
        return $this->hasMany(DoctorInstitution::class, 'doc_id', 'doc_id');
    }
}
