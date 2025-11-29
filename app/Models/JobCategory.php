<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobCategory extends Model
{
   use HasFactory;

   /**
    * The attributes that are mass assignable.
    *
    * @var array<string>
    */
   protected $fillable = [
       'title'
   ];

   /**
    * Get the jobs that belong to this category.
    */
   public function jobs(): BelongsToMany 
   {
       return $this->belongsToMany(JobListing::class);
   }

   /**
    * Get only active jobs in this category.
    */
   public function activeJobs(): BelongsToMany
   {
       return $this->jobs()->active();
   }

   /**
    * Count jobs in this category.
    */
   public function getJobsCount(): int
   {
       return $this->jobs()->count();
   }

   /**
    * Count active jobs in this category.
    */
   public function getActiveJobsCount(): int
   {
       return $this->activeJobs()->count();
   }
}