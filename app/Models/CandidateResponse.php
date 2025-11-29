<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_assignment_id',
        'question_id',
        'response_type',
        'option_id',
        'text_response',
        'code_response',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
    ];

    /**
     * Get the test assignment this response belongs to.
     */
    public function testAssignment(): BelongsTo
    {
        return $this->belongsTo(TestAssignment::class);
    }

    /**
     * Get the question this response is for.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class);
    }

    /**
     * Get the selected option (if applicable).
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
