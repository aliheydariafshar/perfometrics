<?php

namespace App\Services;

use App\Models\Department;
use App\Models\PerformanceScore;
use App\Models\User;
use Carbon\Carbon;

class PerformanceCalculator
{
    private const TASK_COMPLETION_WEIGHT = 0.30;
    private const DEADLINE_ADHERENCE_WEIGHT = 0.25;
    private const PEER_REVIEWS_WEIGHT = 0.25;
    private const TRAINING_COMPLETION_WEIGHT = 0.20;
    private const NEW_EMPLOYEE_MIN_MONTHS = 3;

    public function __construct(
        private readonly SnapshotBuilder $snapshotBuilder,
        private readonly TaskCompletionCalculator $taskCompletionCalculator,
        private readonly DeadlineAdherenceCalculator $deadlineAdherenceCalculator,
        private readonly PeerReviewCalculator $peerReviewCalculator,
        private readonly TrainingCompletionCalculator $trainingCompletionCalculator,
        private readonly ScoreRepository $scoreRepository,
        private readonly ReportService $reportService
    ) {
    }

    private function roundToOneDecimal(float $value): float
    {
        $scaled = $value * 10;
        if ($scaled >= 0) {
            $rounded = floor($scaled + 0.5);
        } else {
            $rounded = ceil($scaled - 0.5);
        }
        return (float) ($rounded / 10);
    }

    public function calculateForEmployee(User $employee): array
    {
        [$taskCompletion, $deadlineAdherence, $peerReview, $trainingCompletion] = $this->computeComponentScores($employee);

        $finalScoreRaw = $this->weightedScore($taskCompletion, $deadlineAdherence, $peerReview, $trainingCompletion);
        $finalScore = $this->applyBusinessRules($employee, $finalScoreRaw);

        $departmentRank = $this->calculateDepartmentRank($employee, $finalScore);
        $score = $this->persistScore(
            $employee,
            $taskCompletion,
            $deadlineAdherence,
            $peerReview,
            $trainingCompletion,
            $finalScore,
            $departmentRank
        );

        return $this->snapshotBuilder->build(
            $employee,
            $taskCompletion,
            $deadlineAdherence,
            $peerReview,
            $trainingCompletion,
            $finalScore,
            $departmentRank,
            $score
        );
    }

    public function generateEmployeeReport(User $employee, ?Carbon $from = null, ?Carbon $to = null): array
    {
        // Ensure a fresh snapshot exists and get the latest values
        $snapshot = $this->calculateForEmployee($employee);
        $report = $this->reportService->buildEmployeeReport($employee, $from, $to);

        return [
            'snapshot' => $snapshot,
            'trend' => $report['trend'],
            'components_trend' => $report['components_trend'],
            'department' => $report['department'],
            'weights' => [
                'task_completion' => 30,
                'deadline_adherence' => 25,
                'peer_reviews' => 25,
                'training_completion' => 20,
            ],
        ];
    }

    private function weightedScore(float $taskCompletion, float $deadlineAdherence, float $peerReview, float $trainingCompletion): float
    {
        return $taskCompletion * self::TASK_COMPLETION_WEIGHT +
            $deadlineAdherence * self::DEADLINE_ADHERENCE_WEIGHT +
            $peerReview * self::PEER_REVIEWS_WEIGHT +
            $trainingCompletion * self::TRAINING_COMPLETION_WEIGHT;
    }

    private function calculateTaskCompletionRate(User $employee): float
    {
        return $this->taskCompletionCalculator->calculate($employee);
    }

    private function calculateDeadlineAdherence(User $employee): float
    {
        return $this->deadlineAdherenceCalculator->calculate($employee);
    }

    private function calculatePeerReviewAverage(User $employee): float
    {
        return $this->peerReviewCalculator->calculate($employee);
    }

    private function calculateTrainingCompletion(User $employee): float
    {
        return $this->trainingCompletionCalculator->calculate($employee);
    }

    private function computeComponentScores(User $employee): array
    {
        $taskCompletion = $this->calculateTaskCompletionRate($employee);
        $deadlineAdherence = $this->calculateDeadlineAdherence($employee);
        $peerReview = $this->calculatePeerReviewAverage($employee);
        $trainingCompletion = $this->calculateTrainingCompletion($employee);

        return [
            $taskCompletion,
            $deadlineAdherence,
            $peerReview,
            $trainingCompletion,
        ];
    }

    private function applyBusinessRules(User $employee, float $finalScoreRaw): float
    {
        $finalScore = $finalScoreRaw;
        // New Employee Rule
        if ($employee->hire_date && Carbon::parse($employee->hire_date)->gt(Carbon::now()->subMonths(self::NEW_EMPLOYEE_MIN_MONTHS))) {
            $finalScore = max($finalScore, 50);
        }
        return $this->roundToOneDecimal($finalScore);
    }

    private function persistScore(
        User $employee,
        float $taskCompletion,
        float $deadlineAdherence,
        float $peerReview,
        float $trainingCompletion,
        float $finalScore,
        ?int $departmentRank
    ): PerformanceScore {
        return $this->scoreRepository->upsertLatest(
            $employee,
            $taskCompletion,
            $deadlineAdherence,
            $peerReview,
            $trainingCompletion,
            $finalScore,
            $departmentRank
        );
    }

    public function departmentSummary(Department $department): array
    {
        $employees = $department->users()->with('performanceScores')->get();
        $scores = $employees->map(fn ($u) => optional($u->performanceScores->sortByDesc('calculated_at')->first())->final_score ?? 0.0);

        $average = $scores->count() ? $this->roundToOneDecimal($scores->avg()) : 0.0;

        $ranked = $employees->map(function ($u) {
            $latest = $u->performanceScores->sortByDesc('calculated_at')->first();
            return [
                'id' => $u->id,
                'name' => $u->name,
                'score' => $latest?->final_score ?? 0.0,
            ];
        })->sortByDesc('score')->values();

        return [
            'department_id' => $department->id,
            'department_name' => $department->name,
            'average_score' => $average,
            'total_employees' => $employees->count(),
            'top_performers' => $ranked->take(3)->all(),
            'improvement_needed' => $ranked->reverse()->take(3)->values()->all(),
        ];
    }

    private function calculateDepartmentRank(User $employee, ?float $employeeScore = null): ?int
    {
        if (!$employee->department) {
            return null;
        }

        if ($employeeScore === null) {
            $employeeScore = null ?? 0.0;
        }

        $employees = $employee->department->users;
        $ranked = $employees->map(function ($u) use ($employee, $employeeScore) {
            if ($u->id === $employee->id) {
                return $employeeScore;
            }

            $latest = $u->performanceScores()->orderByDesc('calculated_at')->first();
            return $latest?->final_score ?? 0.0;
        })->sortDesc()->values();

        $position = $ranked->search($employeeScore);
        return $position === false ? null : ($position + 1);
    }
}
