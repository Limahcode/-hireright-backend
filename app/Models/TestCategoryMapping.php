<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestCategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'category_id',
    ];

    /**
     * Get the associated test.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the associated category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'category_id');
    }
}
