<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $fillable = [
        'product_cat',
        'product_brand',
        'product_title',
        'product_price',
        'product_desc',
        'product_image',
        'product_keywords',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'product_cat', 'cat_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'product_brand', 'brand_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id', 'product_id');
    }
}
