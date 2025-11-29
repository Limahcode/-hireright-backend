<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experience extends Model
{
   use HasFactory;

   /**
    * The attributes that are mass assignable.
    *
    * @var array<string>
    */
   protected $fillable = [
       'user_id',
       'company_name',
       'job_title', 
       'description',
       'location',
       'employment_type',
       'start_date',
       'end_date',
       'is_current'
   ];

   /**
    * The attributes that should be cast.
    *
    * @var array<string, string>
    */
   protected $casts = [
       'start_date' => 'date',
       'end_date' => 'date',
       'is_current' => 'boolean'
   ];

   /**
    * The employment type options.
    * 
    * @var array<string>
    */
   public const EMPLOYMENT_TYPES = [
       'full_time',
       'part_time',
       'self_employed',
       'freelance',
       'contract',
       'internship'
   ];

   /**
    * Get the user that owns the experience.
    */
   public function user(): BelongsTo
   {
       return $this->belongsTo(User::class);
   }

   /**
    * Check if this is a current position.
    */
   public function isCurrentPosition(): bool
   {
       return $this->is_current;
   }

   /**
    * Get the formatted duration of the experience.
    */
   public function getDuration(): string
   {
       $endDate = $this->is_current ? 'Present' : $this->end_date->format('F Y');
       return $this->start_date->format('F Y') . ' - ' . $endDate;
   }

   /**
    * Calculate the duration in months.
    */
   public function getDurationInMonths(): int
   {
       $endDate = $this->is_current ? now() : $this->end_date;
       return $this->start_date->diffInMonths($endDate);
   }
}