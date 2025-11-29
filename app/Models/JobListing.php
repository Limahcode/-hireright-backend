<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobListing extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_listings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'requirements',
        'responsibilities',
        'benefits',
        'employment_type',
        'work_mode',
        'type',
        'positions_available',
        'experience_level',
        'min_years_experience',
        'salary_min',
        'salary_max',
        'salary_currency',
        'hide_salary',
        'location',
        'remote_regions',
        'deadline',
        'is_featured',
        'is_published',
        'reference_code',
        'status',
        'company_id',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'remote_regions' => 'array',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'positions_available' => 'integer',
        'min_years_experience' => 'integer',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'hide_salary' => 'boolean',
        'deadline' => 'datetime',
    ];

    /**
     * Constants for employment types
     */
    public const EMPLOYMENT_TYPES = [
        'full_time',
        'part_time',
        'self_employed',
        'freelance',
        'contract',
        'internship'
    ];

    /**
     * Constants for work modes
     */
    public const WORK_MODES = [
        'remote',
        'hybrid',
        'onsite'
    ];

    /**
     * Constants for status
     */
    public const STATUSES = [
        'draft',
        'published',
        'closed',
        'archived'
    ];

    /**
     * Get the company that owns the job.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that created the job.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the job is active/open
     */
    public function isActive(): bool
    {
        return $this->status === 'published'
            && $this->is_published
            && ($this->deadline === null || $this->deadline->isFuture());
    }

    /**
     * Get the formatted salary range
     */
    public function getSalaryRange(): ?string
    {
        if ($this->hide_salary) {
            return null;
        }

        if ($this->salary_min && $this->salary_max) {
            return number_format($this->salary_min, 0) . ' - ' .
                number_format($this->salary_max, 0) . ' ' .
                $this->salary_currency;
        }

        if ($this->salary_min) {
            return 'From ' . number_format($this->salary_min, 0) . ' ' . $this->salary_currency;
        }

        if ($this->salary_max) {
            return 'Up to ' . number_format($this->salary_max, 0) . ' ' . $this->salary_currency;
        }

        return null;
    }

    /**
     * Scope a query to only include active jobs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>', now());
            });
    }

    /**
     * Scope a query to only include featured jobs.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
