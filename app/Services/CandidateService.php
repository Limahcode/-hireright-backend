<?php

namespace App\Services;

use App\Models\User;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Certification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // ← ADD THIS IMPORT

class CandidateService
{
    public function storeProfileData(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $now = now();

            // Store Education Records
            if (!empty($data['education'])) {
                $educationRecords = collect($data['education'])->map(function ($item) use ($userId, $now) {
                    return array_merge($item, [
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
                Education::insert($educationRecords->toArray());
            }

            // Store Experience Records
            if (!empty($data['experiences'])) {
                $experienceRecords = collect($data['experiences'])->map(function ($item) use ($userId, $now) {
                    return array_merge($item, [
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
                Experience::insert($experienceRecords->toArray());
            }

            // Store Certification Records
            if (!empty($data['certifications'])) {
                $certificationRecords = collect($data['certifications'])->map(function ($item) use ($userId, $now) {
                    return array_merge($item, [
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
                Certification::insert($certificationRecords->toArray());
            }

            return $this->getProfileData($userId);
        });
    }  

    public function getProfileData(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        $user = User::findOrFail($userId);

        $education = Education::where('user_id', $userId)
            ->orderBy('is_current', 'desc')
            ->orderBy('end_date', 'desc')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($edu) {
                return [
                    'id' => $edu->id,
                    'institution' => $edu->institution,
                    'degree' => $edu->degree,
                    'field_of_study' => $edu->field_of_study,
                    'location' => $edu->location,
                    'start_date' => $edu->start_date->format('Y-m-d'),
                    'end_date' => $edu->end_date?->format('Y-m-d'),
                    'is_current' => $edu->is_current,
                    'activities' => $edu->activities,
                    'description' => $edu->description,
                ];
            });

        $experiences = Experience::where('user_id', $userId)
            ->orderBy('is_current', 'desc')
            ->orderBy('end_date', 'desc')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'company_name' => $exp->company_name,
                    'job_title' => $exp->job_title,
                    'description' => $exp->description,
                    'location' => $exp->location,
                    'employment_type' => $exp->employment_type,
                    'start_date' => $exp->start_date->format('Y-m-d'),
                    'end_date' => $exp->end_date?->format('Y-m-d'),
                    'is_current' => $exp->is_current,
                ];
            });

        $certifications = Certification::where('user_id', $userId)
            ->orderBy('issue_date', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'name' => $cert->name,
                    'organization' => $cert->organization,
                    'issue_date' => $cert->issue_date->format('Y-m-d'),
                    'expiration_date' => $cert->expiration_date?->format('Y-m-d'),
                    'has_expiry' => $cert->has_expiry,
                    'is_expired' => $cert->isExpired(),
                ];
            });

        return [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'bio' => $user->bio,
                'title' => $user->title,
                // ADD DOCUMENT URLs
                'profile_image_url' => $user->profile_image ? Storage::url($user->profile_image) : null,
                'cover_image_url' => $user->cover_image ? Storage::url($user->cover_image) : null,
                'resume_url' => $user->resume ? Storage::url($user->resume) : null,
                'cover_letter_file_url' => $user->cover_letter_file ? Storage::url($user->cover_letter_file) : null,
                'portfolio_url' => $user->portfolio ? Storage::url($user->portfolio) : null,
            ],
            'education' => $education,
            'experiences' => $experiences,
            'certifications' => $certifications,
        ];
    }
}