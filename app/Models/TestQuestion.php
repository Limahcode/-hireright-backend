<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'settings' => 'json',
    ];

    /**
     * Get the test this question belongs to.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
