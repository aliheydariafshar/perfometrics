<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class TrainingCompletionCalculator
{
    public function calculate(User $employee): float
    {
        $currentYear = Carbon::now()->year;

        $base = $employee->trainings()
            ->where('year', $currentYear)
            ->where('required', true);

        $total = (clone $base)->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = (clone $base)
            ->whereNotNull('employee_training.completed_date')
            ->count();

        return ($completed / $total) * 100;
    }
}
