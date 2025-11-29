<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpec extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'option_label',
        'val',
        'data_type',
        'input_type',
        'product_id',
    ];

    // Define the relationship with the Product model
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Clone method similar to the Java code
    public function cloneObject()
    {
        return new self([
            'name' => $this->name,
            'label' => $this->label,
            'option_label' => $this->option_label,
            'val' => $this->val,
            'data_type' => $this->data_type,
            'input_type' => $this->input_type,
        ]);
    }
}
