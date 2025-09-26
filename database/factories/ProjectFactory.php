<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'description' => fake()->optional()->paragraph(),
            'department_id' => Department::factory(),
            'start_date' => fake()->optional()->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
            'end_date' => fake()->optional()->dateTimeBetween('now', '+1 year')?->format('Y-m-d'),
        ];
    }
}
