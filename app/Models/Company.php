<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'about',
        'website',
        'address',
        'city',
        'owner_id',
        'state',
        'country',
        'postal_code',
        'size_min',
        'size_max',
        'industry_code',
        'is_verified',
        'is_featured',
        'status',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'tiktok_url'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size_min' => 'integer',
        'size_max' => 'integer',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean'
    ];

    /**
     * The possible company statuses.
     */
    public const STATUSES = [
        'active',
        'inactive',
        'suspended'
    ];

    /**
     * Get company size range as string.
     */
    public function getSizeRange(): ?string
    {
        if (!$this->size_min && !$this->size_max) {
            return null;
        }

        if ($this->size_min && $this->size_max) {
            return "{$this->size_min} - {$this->size_max} employees";
        }

        if ($this->size_min) {
            return "{$this->size_min}+ employees";
        }

        return "Up to {$this->size_max} employees";
    }

    /**
     * Get full address as string.
     */
    public function getFullAddress(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Get active social media links.
     */
    public function getActiveSocialLinks(): array
    {
        return array_filter([
            'linkedin' => $this->linkedin_url,
            'twitter' => $this->twitter_url,
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'youtube' => $this->youtube_url,
            'tiktok' => $this->tiktok_url
        ]);
    }

    /**
     * Scope a query to only include active companies.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include verified companies.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include featured companies.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // In Company model
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staff()
    {
        return $this->hasMany(CompanyStaff::class);
    }
}
