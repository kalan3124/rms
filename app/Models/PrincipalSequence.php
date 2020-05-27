<?php
namespace App\Models;

class PrincipalSequence extends Base{
     protected $table = 'principal_sequence';

     protected $primaryKey = 'pri_seq_id';

     protected $fillable = [
          'principal_id',
          'u_id',
          'sequence_no'
     ];
}
?>