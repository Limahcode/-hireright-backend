<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Proficiency levels for user skills
     */
    public const PROFICIENCY_LEVELS = [
        'beginner',
        'intermediate',
        'advanced',
        'expert'
    ];

    /**
     * Get the jobs that require this skill.
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class, 'job_listing_skills');
    }

    /**
     * Get the users that have this skill.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills')
                    ->withPivot('years_experience', 'proficiency_level')
                    ->withTimestamps();
    }

    /**
     * Get the count of jobs requiring this skill.
     */
    public function getJobsCount(): int
    {
        return $this->jobs()->count();
    }

    /**
     * Get the count of users having this skill.
     */
    public function getUsersCount(): int
    {
        return $this->users()->count();
    }
}