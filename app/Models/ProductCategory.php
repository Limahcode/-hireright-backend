<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'tags',
        'parent_id',
        'store_id',
        'is_featured'
    ];

    // A category can have sub-categories
    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    // Also helpful to add:
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    // A category can belong to a store (optional)
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

}
