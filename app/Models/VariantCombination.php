<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantCombination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'current_price',
        'sales_price',
        'on_sales',
        'sku',
        'sku_quantity',
        'barcode',
        'quantity',
        'track_quantity',
        'reorder_point',
        'active',
        'product_id',
    ];

    /**
     * Clone the variant combination.
     */
    public function cloneObject()
    {
        return new self([
            'name' => $this->name,
            'code' => $this->code,
            'current_price' => $this->current_price,
            'sales_price' => $this->sales_price,
            'on_sales' => $this->on_sales,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'quantity' => 0, // Reset quantity for cloning
            'track_quantity' => $this->track_quantity,
            'reorder_point' => $this->reorder_point,
            'active' => $this->active
        ]);
    }
}
