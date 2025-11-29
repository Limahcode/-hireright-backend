<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'variant_name',
        'entry_value',
    ];

    /**
     * An order item variant belongs to an order item.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
