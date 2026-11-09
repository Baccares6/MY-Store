<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $primaryKey = 'brand_id';
    public $timestamps = false;

    protected $fillable = [
        'brand_title',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_brand', 'brand_id');
    }
}
