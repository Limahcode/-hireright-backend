<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGeneratedTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'prompt',
        'parameters',
        'generation_status',
    ];

    protected $casts = [
        'parameters' => 'json',
    ];

    /**
     * Get the associated test.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
