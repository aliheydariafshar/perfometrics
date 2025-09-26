<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMilestoneFactory extends Factory
{
    protected $model = ProjectMilestone::class;

    /**
     * @throws \Exception
     */
    public function definition(): array
    {
        $due = fake()->optional(0.9)->dateTimeBetween('-1 year', '+6 months');
        $completed = null;
        if ($due && fake()->boolean(70)) {
            $completed = fake()->dateTimeBetween((clone $due)->modify('-1 month'), (clone $due)->modify('+1 month'));
        } elseif (fake()->boolean(15)) {
            $completed = fake()->dateTimeBetween('-1 year', 'now');
        }

        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'due_date' => $due?->format('Y-m-d'),
            'completed_date' => $completed?->format('Y-m-d'),
        ];
    }
}
