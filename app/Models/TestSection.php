<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'title',
        'description',
        'order',
        'time_limit',
        'instructions',
        'is_downloadable',
        'submission_type',
    ];

    protected $casts = [
        'order' => 'integer',
        'time_limit' => 'integer',
        'is_downloadable' => 'boolean',
    ];

    /**
     * Get the test this section belongs to.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the questions in this section.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class);
    }
}
