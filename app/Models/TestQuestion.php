<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question_text',
        'question_type',
        'points',
        'order',
        'settings',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'settings' => 'array',
    ];

    /**
     * Question types
     */
    public const TYPES = [
        'multiple_choice',
        'single_choice',
        'text',
        'code',
        'file_upload',
        'information_only'
    ];

    /**
     * Get the test this question belongs to.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('order');
    }

    /**
     * Get the attachments for this question.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(QuestionAttachment::class, 'question_id');
    }

    /**
     * Get the responses for this question.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class, 'question_id');
    }
}