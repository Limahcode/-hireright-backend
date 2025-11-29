<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'active',
        'variant_id',
        'product_id',
    ];

    /**
     * Clone the product variant option.
     */
    public function cloneObject()
    {
        return new self([
            'name' => $this->name,
            'active' => $this->active,
        ]);
    }
}
