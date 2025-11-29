<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'signup_strategy' => 'form', // default signup strategy
            'reg_channel' => 'web', // default registration channel
            'referral_code' => Str::random(8), // random referral code
            'status' => 'active', // default status
            'firebase_device_token' => Str::random(20), // fake token
            'dob' => fake()->date(),
            'email_otp' => null, // or use fake()->randomNumber(4) if needed
            'phone_otp' => null,
            'password_otp' => null,
            'login_otp' => null,
            'email_verified' => true,
            'phone_verified' => true,
            'app_role' => 'vendor', // default role, change as needed
            'last_seen' => now(),
            'login_count' => fake()->numberBetween(1, 100),
            'password' => static::$password ??= Hash::make('password'), // Default hashed password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified' => false,
            'email_verified_at' => null,
        ]);
    }
}
