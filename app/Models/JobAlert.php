<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class JobAlert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'keywords',
        'locations',
        'job_types',
        'experience_levels',
        'salary_min',
        'skills',
        'companies',
        'frequency',
        'is_active',
        'last_sent_at',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'keywords' => 'array',
        'locations' => 'array',
        'job_types' => 'array',
        'experience_levels' => 'array',
        'skills' => 'array',
        'companies' => 'array',
        'salary_min' => 'decimal:2',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the job alert.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
