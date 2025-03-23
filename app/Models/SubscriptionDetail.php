<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDetail extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
    protected $fillable = [
        'customizable',
        'guest_count',
        'guest_approve',
        'music',
        'video',
        'image',
        'qrcode',
    ];

    /**
     * Define the relationship with Subscription.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'subscription_details_id');
    }
}
