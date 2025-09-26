<?php

namespace App\Services\BusinessRules;

use App\Models\User;
use Carbon\Carbon;

readonly class NewEmployeeMinimumRule implements BusinessRule
{
    public function __construct(private int $minMonths, private float $minScore)
    {
    }

    public function apply(User $employee, float $currentScore): float
    {
        if ($employee->hire_date && Carbon::parse($employee->hire_date)->gt(Carbon::now()->subMonths($this->minMonths))) {
            return max($currentScore, $this->minScore);
        }
        return $currentScore;
    }
}
