<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @throws \Exception
     */
    public function definition(): array
    {
        $due = fake()->optional(0.8)->dateTimeBetween('-6 months', '+3 months');
        $completed = null;
        if ($due && fake()->boolean(70)) {
            $completed = fake()->dateTimeBetween((clone $due)->modify('-1 month'), (clone $due)->modify('+1 month'));
        } elseif (fake()->boolean(20)) {
            $completed = fake()->dateTimeBetween('-6 months', 'now');
        }

        return [
            'project_id' => Project::factory(),
            'assignee_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'due_date' => $due?->format('Y-m-d'),
            'completed_date' => $completed?->format('Y-m-d'),
        ];
    }
}
