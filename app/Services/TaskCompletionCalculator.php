<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskCompletionCalculator
{
    public function calculate(User $employee): float
    {
        $query = Task::query()
            ->where('assignee_id', $employee->id)
            ->whereNotNull('due_date')
            ->select(['due_date', 'completed_date'])
            ->orderBy('id');

        $totalWithDueDates = 0;
        $onTime = 0;
        $lateHalfCredit = 0;

        foreach ($query->cursor() as $task) {
            $totalWithDueDates++;
            if (!$task->completed_date) {
                continue;
            }
            if ($task->due_date && $task->completed_date <= $task->due_date) {
                $onTime++;
            } else {
                $lateHalfCredit++;
            }

        }

        if ($totalWithDueDates === 0) {
            return 0.0;
        }

        return (($onTime + 0.5 * $lateHalfCredit) / $totalWithDueDates) * 100;
    }
}
