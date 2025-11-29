<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'active',
        'product_id',
    ];

    /**
     * A product variant belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A product variant has many options (like size, color, etc.).
     */
    public function variantOptions()
    {
        return $this->hasMany(ProductVariantOption::class, 'variant_id');
    }

    /**
     * Clone the product variant, useful for duplicating variants.
     */
    public function cloneObject()
    {
        $clonedOptions = $this->variantOptions->map(function ($option) {
            return $option->cloneObject();
        });

        return new self([
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'active' => $this->active,
            'variantOptions' => $clonedOptions,
        ]);
    }
}
