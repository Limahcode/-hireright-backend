<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_name',
        'slug',
        'address',
        'code',
        'region_code',
        'country_code',
        'phone',
        'email',
        'url',
        'status',
        'apply_vat',
        'vat_percent',
        'owner_id',
    ];

    // Relationship with user (vendor)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
