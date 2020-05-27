<?php

namespace App\Models;

class InstitutionAssignForDoctor extends Base
{
    protected $table = 'institution_assignment_for_doctors';

    protected $primaryKey = 'ins_ass_id';

    protected $fillable = [
        'doc_id','ins_id','twn_id'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function institution(){
        return $this->belongsTo(Institution::class,'ins_id','ins_id');
    }

    public function town(){
        return $this->belongsTo(Town::class,'twn_id','twn_id');
    }
}
