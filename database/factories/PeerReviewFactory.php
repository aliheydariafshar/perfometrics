<?php

namespace Database\Factories;

use App\Models\PeerReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeerReviewFactory extends Factory
{
    protected $model = PeerReview::class;

    public function definition(): array
    {
        return [
            'reviewee_id' => User::factory(),
            'reviewer_id' => User::factory(),
            'score' => fake()->numberBetween(1, 10),
            'comments' => fake()->optional()->sentence(),
        ];
    }
}
