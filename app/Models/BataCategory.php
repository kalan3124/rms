<?php
namespace App\Models;

class BataCategory extends Base{

     protected $table = 'bata_category';

     protected $primaryKey = 'btc_id';

     protected $fillable = [
          'btc_category','btc_code'
     ];

}