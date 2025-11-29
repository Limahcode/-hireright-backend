<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'job_id',
        'user_id',
        'company_id',
        'status',
        'cover_letter',
        'answers',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'answers' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Application status constants
     */
    public const STATUS_APPLIED = 'applied';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const STATUS_TEST_INVITED = 'test_invited';
    public const STATUS_TEST_COMPLETED = 'test_completed';
    public const STATUS_OFFERED = 'offered';
    public const STATUS_HIRED = 'hired';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * All possible statuses
     */
    public const STATUSES = [
        self::STATUS_APPLIED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_SHORTLISTED,
        self::STATUS_INTERVIEW_SCHEDULED,
        self::STATUS_TEST_INVITED,
        self::STATUS_TEST_COMPLETED,
        self::STATUS_OFFERED,
        self::STATUS_HIRED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN
    ];

    /**
     * Active application statuses (not terminal states)
     */
    public const ACTIVE_STATUSES = [
        self::STATUS_APPLIED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_SHORTLISTED,
        self::STATUS_INTERVIEW_SCHEDULED,
        self::STATUS_TEST_INVITED,
        self::STATUS_TEST_COMPLETED,
        self::STATUS_OFFERED
    ];

    /**
     * Terminal application statuses
     */
    public const TERMINAL_STATUSES = [
        self::STATUS_HIRED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN
    ];

    /**
     * Valid status transitions
     */
    public const STATUS_TRANSITIONS = [
        self::STATUS_APPLIED => [
            self::STATUS_UNDER_REVIEW,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_UNDER_REVIEW => [
            self::STATUS_SHORTLISTED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_SHORTLISTED => [
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_TEST_INVITED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_INTERVIEW_SCHEDULED => [
            self::STATUS_TEST_INVITED,
            self::STATUS_OFFERED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_TEST_INVITED => [
            self::STATUS_TEST_COMPLETED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_TEST_COMPLETED => [
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_OFFERED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ],
        self::STATUS_OFFERED => [
            self::STATUS_HIRED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN
        ]
    ];

    /**
     * Get the job listing that owns the application.
     */
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    /**
     * Get the user that owns the application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who reviewed the application.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the application can transition to the given status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    /**
     * Check if the application is in a terminal state.
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    /**
     * Check if the application is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    /**
     * Check if the application has been reviewed.
     */
    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    /**
     * Check if the application was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the application was withdrawn.
     */
    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }

    /**
     * Check if the candidate was hired.
     */
    public function isHired(): bool
    {
        return $this->status === self::STATUS_HIRED;
    }

    /**
     * Get the application duration in days.
     */
    public function getDurationInDays(): int
    {
        return $this->created_at->diffInDays($this->updated_at);
    }

    /**
     * Scope a query to only include active applications.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope a query to only include applications in terminal states.
     */
    public function scopeTerminal($query)
    {
        return $query->whereIn('status', self::TERMINAL_STATUSES);
    }

    /**
     * Scope a query to only include rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope a query to only include hired applications.
     */
    public function scopeHired($query)
    {
        return $query->where('status', self::STATUS_HIRED);
    }

    /**
     * Scope a query to only include withdrawn applications.
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('status', self::STATUS_WITHDRAWN);
    }

    /**
     * Scope a query to only include applications that need review.
     */
    public function scopeNeedsReview($query)
    {
        return $query->whereNull('reviewed_at');
    }

    /**
     * Scope a query to only include applications for a specific job.
     */
    public function scopeForJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope a query to only include applications by a specific candidate.
     */
    public function scopeByCandidate($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
