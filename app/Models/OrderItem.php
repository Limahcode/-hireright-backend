<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_barcode',
        'combination_name',
        'product_category_id',
        'price',
        'qty',
        'vat',
        'vat_exempted',
        'on_sales'
    ];

    /**
     * An order item belongs to an order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * An order item belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * An order item has many variants.
     */
    public function variants()
    {
        return $this->hasMany(OrderItemVariant::class);
    }
}
