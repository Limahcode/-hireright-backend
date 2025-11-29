<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserAndCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Seed users with different roles
        $users = [
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'app_role' => 'admin'
            ],
            [
                'first_name' => 'Employer',
                'last_name' => 'User',
                'email' => 'employer@example.com',
                'password' => Hash::make('password'),
                'app_role' => 'employer'
            ],
            [
                'first_name' => 'Job Seeker',
                'last_name' => 'User',
                'email' => 'jobseeker@example.com',
                'password' => Hash::make('password'),
                'app_role' => 'candidate'
            ]
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']], // Unique email constraint
                $userData // The rest of the data
            );
        }

        // Fetch the employer user
        $employerUser = User::where('email', 'employer@example.com')->first();
        
        // List of sample companies
        $companies = [
            [
                'name' => 'Tech Innovators Ltd',
                'slug' => Str::slug('Tech Innovators Ltd'),
                'email' => 'contact@techinnovators.com',
                'phone' => '08012345678',
                'about' => 'Leading technology solutions provider',
                'website' => 'https://techinnovators.com',
                'address' => '14 Innovation Drive',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'industry_code' => 'TECH',
                'size_min' => 50,
                'size_max' => 200
            ],
            [
                'name' => 'Global Finance Corp',
                'slug' => Str::slug('Global Finance Corp'),
                'email' => 'info@globalfinance.com',
                'phone' => '08023456789',
                'about' => 'International financial services company',
                'website' => 'https://globalfinance.com',
                'address' => '25 Marina Street',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'industry_code' => 'FIN',
                'size_min' => 100,
                'size_max' => 500
            ],
            [
                'name' => 'Green Energy Solutions',
                'slug' => Str::slug('Green Energy Solutions'),
                'email' => 'contact@greenenergy.com',
                'phone' => '08034567890',
                'about' => 'Renewable energy and sustainability experts',
                'website' => 'https://greenenergy.com',
                'address' => '7 Sustainability Road',
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'industry_code' => 'ENERGY',
                'size_min' => 20,
                'size_max' => 100
            ]
        ];

        // Create companies and add staff
        foreach ($companies as $companyData) {
            $company = Company::firstOrCreate(
                ['slug' => $companyData['slug']],
                array_merge($companyData, [
                    'status' => 'active',
                    'is_verified' => true,
                    'is_featured' => false,
                    'postal_code' => null,
                    'linkedin_url' => null,
                    'twitter_url' => null,
                    'facebook_url' => null,
                    'instagram_url' => null,
                    'youtube_url' => null,
                    'tiktok_url' => null
                ])
            );

            // Add the employer user as a staff member in the company_staff table
            DB::table('company_staff')->updateOrInsert(
                [
                    'user_id' => $employerUser->id,
                    'company_id' => $company->id
                ],
                [
                    'job_title' => 'HR Manager',
                    'department' => 'Human Resources',
                    'permissions' => json_encode(['manage_jobs', 'view_applications', 'manage_staff']),
                    'is_admin' => true,
                    'notification_preferences' => json_encode([
                        'email' => true,
                        'in_app' => true
                    ]),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}