<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::updateOrCreate(
            [
                'gateway_code' => 'paystack',
                'currency_code' => 'NGN',
            ],
            [
                'gateway_name' => 'Paystack',
                'status' => 'active',
                'is_default' => true,
                // Paystack-specific fields
                'live_secret_key' => null,
                'live_public_key' => null,
                'test_secret_key' => 'sk_test_54c9a1daa79ad9be6a22b8a3d8e464747b06b144',
                'test_public_key' => 'pk_test_dedaef9dbf7169ebfaf95dc4379327d7b7e00b6e',
                'live_validated' => true,
                'test_validated' => true,
                'capped_at' => 2000, // Maximum charge amount
                'percent' => 1.5, // Paystack percentage fee
                'surcharge' => 100, // Additional flat fee, if applicable
                // Other common fields
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
