<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'creator_type',
        'creator_id',
        'time_limit',
        'passing_score',
        'is_active',
        'submission_type',
        'visibility_type',
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'passing_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the creator of the test (HireRight or a Company).
     */
    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
 * Get the questions for this test.
 */
public function questions(): HasMany
{
    return $this->hasMany(TestQuestion::class)->orderBy('order');
}
}
