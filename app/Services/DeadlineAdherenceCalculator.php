<?php

namespace App\Services;

use App\Models\ProjectMilestone;
use App\Models\User;
use Carbon\Carbon;

class DeadlineAdherenceCalculator
{
    public function calculate(User $employee): float
    {
        $oneYearAgo = Carbon::now()->subYear();

        $query = ProjectMilestone::query()
            ->whereHas('project.tasks', function ($q) use ($employee, $oneYearAgo) {
                $q->where('assignee_id', $employee->id)
                  ->where('created_at', '>=', $oneYearAgo);
            })
            ->where('due_date', '>=', $oneYearAgo)
            ->select(['due_date', 'completed_date'])
            ->orderBy('id');

        $total = 0;
        $met = 0;
        foreach ($query->cursor() as $m) {
            $total++;
            if ($m->completed_date && $m->due_date && $m->completed_date <= $m->due_date) {
                $met++;
            }
        }

        if ($total === 0) {
            return 0.0;
        }

        return ($met / $total * 100);
    }
}
