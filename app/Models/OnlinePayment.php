<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'gateway_ref',
        'gateway_code',
        'customer_email',
        'verified',
        'viewed',
        'settled',
        'pass_charges',
        'last_verified',
        'initiated',
        'completed',
        'amount',
        'gateway_fee',
        'currency_code',
        'status',
        'customer_id',
        'order_id',
        'store_id',
    ];

    /**
     * An online payment belongs to an order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * An online payment belongs to a store.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
