<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestTagMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'tag_id',
    ];

    /**
     * Get the associated test.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the associated tag.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(TestTag::class, 'tag_id');
    }
}
