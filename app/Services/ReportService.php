<?php

namespace App\Services;

use App\Models\PerformanceScore;
use App\Models\User;
use Carbon\Carbon;

readonly class ReportService
{
    public function __construct(private ScoreRepository $scores, private DepartmentAnalytics $dept)
    {
    }

    public function buildEmployeeReport(User $employee, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $scoresQuery = $this->scores->historyQuery($employee);
        if ($from) {
            $scoresQuery->where('calculated_at', '>=', $from);
        }
        if ($to) {
            $scoresQuery->where('calculated_at', '<=', $to);
        }

        $historical = $scoresQuery->get();

        $trend = $historical->map(function (PerformanceScore $ps) {
            return [
                'date' => $ps->calculated_at?->toIso8601String(),
                'score' => $ps->final_score,
            ];
        })->values()->all();

        $componentsTrend = $historical->map(function (PerformanceScore $ps) {
            return [
                'date' => $ps->calculated_at?->toIso8601String(),
                'breakdown' => [
                    'task_completion' => $ps->task_completion,
                    'deadline_adherence' => $ps->deadline_adherence,
                    'peer_reviews' => $ps->peer_reviews,
                    'training_completion' => $ps->training_completion,
                ],
            ];
        })->values()->all();

        $departmentContext = null;
        if ($employee->department) {
            $departmentContext = $this->dept->summary($employee->department);
        }

        return [
            'trend' => $trend,
            'components_trend' => $componentsTrend,
            'department' => $departmentContext,
        ];
    }
}
