<?php

namespace App\Services\BusinessRules;

use App\Models\User;

interface BusinessRule
{
    public function apply(User $employee, float $currentScore): float;
}
