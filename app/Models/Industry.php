<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
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
    * Get the companies in this industry.
    */
   public function companies(): HasMany
   {
       return $this->hasMany(Company::class);
   }

   /**
    * Get active companies in this industry.
    */
   public function activeCompanies(): HasMany
   {
       return $this->companies()->active();
   }

}