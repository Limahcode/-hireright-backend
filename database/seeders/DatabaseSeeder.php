<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SkillSeeder::class);
        $this->call(UserAndCompanySeeder::class);
        //$this->call(BlogSeeder::class);
        $this->call(PaymentGatewaySeeder::class);
        $this->call(NotificationSeeder::class);
    }
}
