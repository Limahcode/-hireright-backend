<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageTestMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id',
        'test_id',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the recruitment stage associated with this mapping.
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(RecruitmentStage::class, 'stage_id');
    }

    /**
     * Get the test associated with this mapping.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
