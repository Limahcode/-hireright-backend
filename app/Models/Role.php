<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
   use HasFactory;

   /**
    * The attributes that are mass assignable.
    *
    * @var array<string>
    */
   protected $fillable = [
       'name'
   ];

   /**
    * The common role names/types.
    */
   public const ROLES = [
       'admin',
       'employer',
       'candidate'
   ];

   /**
    * Get the users that have this role.
    */
   public function users(): BelongsToMany
   {
       return $this->belongsToMany(User::class);
   }

   /**
    * Determine if this role is an admin role.
    */
   public function isAdmin(): bool
   {
       return $this->name === 'admin';
   }

   /**
    * Determine if this role is an employer role.
    */
   public function isEmployer(): bool
   {
       return $this->name === 'employer';
   }

   /**
    * Determine if this role is a job seeker role.
    */
   public function isCandidate(): bool
   {
       return $this->name === 'candidate';
   }
}