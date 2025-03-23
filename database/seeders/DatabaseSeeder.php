<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\SubscriptionDetail;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionDetail::factory()->count(10)->create();

        // Generate Subscriptions that reference SubscriptionDetails
        Subscription::factory()->count(10)->create();
    }
}
