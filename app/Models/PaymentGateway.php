<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $table = 'payment_gateways';

    protected $fillable = [
        'gateway_name',
        'gateway_code',
        'currency_code',
        'status',
        'is_default',
        'live_secret_key',
        'live_public_key',
        'test_secret_key',
        'test_public_key',
        'live_validated',
        'test_validated',
        'capped_at',
        'percent',
        'surcharge',
        'live_encryption_key',
        'test_encryption_key'
    ];
    // Timestamps
    protected $dates = ['created_at', 'updated_at'];
}
