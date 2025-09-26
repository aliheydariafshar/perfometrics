<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'title' => 'Training: '.fake()->words(3, true),
            'required' => fake()->boolean(70),
            'year' => fake()->numberBetween(now()->year - 1, now()->year + 1),
        ];
    }
}
