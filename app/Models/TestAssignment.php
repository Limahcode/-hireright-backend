<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'candidate_id',
        'stage_test_id',
        'status',
        'started_at',
        'completed_at',
        'expires_at',
        'score',
        'passed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'score' => 'decimal:2',
        'passed' => 'boolean',
    ];

    /**
     * Get the test associated with this assignment.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the candidate who is assigned this test.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the stage-test mapping this assignment is linked to.
     */
    public function stageTest(): BelongsTo
    {
        return $this->belongsTo(StageTestMapping::class, 'stage_test_id');
    }
}
