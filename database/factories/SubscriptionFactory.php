<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100), // Random price between 10 and 100
            'duration' => $this->faker->numberBetween(1, 12), // Random duration (in months)
            'subscription_details_id' => SubscriptionDetail::factory(), // Link to SubscriptionDetail factory
        ];
    }
}
