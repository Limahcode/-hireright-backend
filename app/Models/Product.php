<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'title',
        'slug',
        'description',
        'free_delivery',
        'exempt_vat',
        'has_variants',
        'seo_url',
        'short_url',
        'seo_desc',
        'seo_tags',
        'seo_title',
        'tags',
        'shipping_duration_max',
        'shipping_duration_min',
        'shipping_duration_metric',
        'weight',
        'price',
        'qty',
        'status',
        'is_featured',
        'category_id',
        'pricing_id',
        'inventory_id',
        'relevant_blog_url',
    ];

    protected $dates = ['deleted_at'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id'); 
    }

    public function subCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'sub_category_id');
    }

    public function specs()
    {
        return $this->hasMany(ProductSpec::class, 'product_id');
    }

}
