<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'order_desc',
        'discount_code',
        'platform',
        'currency_code',
        'payment_option',
        'delivery_option',
        'customer_json',
        'delivery_json',
        'total_qty',
        'total',
        'subtotal',
        'total_discount',
        'loyalty_discount',
        'total_paid',
        'balance',
        'vat',
        'status',
        'customer_id',
        'customer_email',
        'store_id',
        'staged',
        'service_charge',
        'gateway_fee'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * An order has many items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * An order belongs to a store.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function onlinePayments()
    {
        return $this->hasMany(OnlinePayment::class);
    }
    
}
