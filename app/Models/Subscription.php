<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'subscription_details_id',
    ];

    /**
     * Define the relationship with SubscriptionDetail.
     */
    public function subscriptionDetail()
    {
        return $this->belongsTo(SubscriptionDetail::class, 'subscription_details_id');
    }

    /**
     * Define the relationship with User (one subscription can belong to many users).
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
