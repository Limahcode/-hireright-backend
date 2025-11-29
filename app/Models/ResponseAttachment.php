<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_response_id',
        'file_path',
        'disk',
    ];

    protected $casts = [];

    /**
     * Get the candidate response associated with this attachment.
     */
    public function candidateResponse(): BelongsTo
    {
        return $this->belongsTo(CandidateResponse::class);
    }
}
