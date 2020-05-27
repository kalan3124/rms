<?php
namespace App\Models;

class TeamProduct extends Base{
    protected $table = 'team_products';

    protected $primaryKey = 'tmp_id';

    protected $fillable = [
        'tm_id','product_id'
    ];
    
    public function team()
    {
        return $this->belongsTo(Team::class, 'tm_id', 'tm_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}