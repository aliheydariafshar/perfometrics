<?php

namespace Database\Factories;

use App\Models\EmployeeTraining;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeTrainingFactory extends Factory
{
    protected $model = EmployeeTraining::class;

    public function definition(): array
    {
        return [
            'assigned_date' => Carbon::now(),
            'completed_date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'training_id' => Training::factory(),
        ];
    }
}
