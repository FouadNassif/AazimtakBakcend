<?php

namespace Database\Factories;

use App\Models\SubscriptionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionDetailFactory extends Factory
{
    protected $model = SubscriptionDetail::class;

    public function definition()
    {
        return [
            'customizable' => $this->faker->boolean, // Random true/false for customization
            'guest_count' => $this->faker->numberBetween(1, 500), // Random guest count
            'guest_approve' => $this->faker->boolean, // Random approval status
            'music' => $this->faker->boolean, // Random true/false for music
            'video' => $this->faker->boolean, // Random true/false for video
            'image' => $this->faker->boolean, // Random true/false for image
            'qrcode' => $this->faker->boolean, // Random true/false for QR code
        ];
    }
}
