<?php

namespace App\Services;

use App\Models\PerformanceScore;
use App\Models\User;

class SnapshotBuilder
{
    public function build(
        User $employee,
        float $taskCompletion,
        float $deadlineAdherence,
        float $peerReview,
        float $trainingCompletion,
        float $finalScore,
        ?int $departmentRank,
        PerformanceScore $score
    ): array {
        return [
            'employee_id' => $employee->id,
            'name' => $employee->name,
            'department' => $employee->department?->name,
            'performance_score' => $finalScore,
            'breakdown' => [
                'task_completion' => $this->roundToOneDecimal($taskCompletion),
                'deadline_adherence' => $this->roundToOneDecimal($deadlineAdherence),
                'peer_reviews' => $this->roundToOneDecimal($peerReview),
                'training_completion' => $this->roundToOneDecimal($trainingCompletion),
            ],
            'department_rank' => $departmentRank,
            'total_employees_in_department' => $employee->department?->users()->count() ?? 0,
            'last_calculated' => $score->calculated_at?->toIso8601String(),
        ];
    }

    private function roundToOneDecimal(float $value): float
    {
        $scaled = $value * 10;
        $rounded = $scaled >= 0 ? floor($scaled + 0.5) : ceil($scaled - 0.5);
        return (float) ($rounded / 10);
    }
}
