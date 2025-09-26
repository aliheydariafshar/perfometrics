<?php

namespace Database\Factories;

use App\Models\PerformanceScore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceScoreFactory extends Factory
{
    protected $model = PerformanceScore::class;

    public function definition(): array
    {
        $task = fake()->randomFloat(1, 0, 100);
        $deadline = fake()->randomFloat(1, 0, 100);
        $peer = fake()->randomFloat(1, 0, 100);
        $training = fake()->randomFloat(1, 0, 100);
        $weighted = ($task * 0.30) + ($deadline * 0.25) + ($peer * 0.25) + ($training * 0.20);
        $final = (float) number_format($weighted, 1, '.', '');

        return [
            'user_id' => User::factory(),
            'task_completion' => $task,
            'deadline_adherence' => $deadline,
            'peer_reviews' => $peer,
            'training_completion' => $training,
            'final_score' => $final,
            'department_rank' => fake()->optional(0.5)->numberBetween(1, 50),
            'calculated_at' => now(),
        ];
    }
}
