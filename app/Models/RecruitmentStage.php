<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'name',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the job posting this stage belongs to.
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
