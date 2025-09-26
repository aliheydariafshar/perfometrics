<?php

namespace App\Services;

use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\BusinessRules\BusinessRule;
use Carbon\Carbon;

class PerformanceCalculator
{
    private array $weights;

    public function __construct(
        private readonly SnapshotBuilder $snapshotBuilder,
        private readonly TaskCompletionCalculator $taskCompletionCalculator,
        private readonly DeadlineAdherenceCalculator $deadlineAdherenceCalculator,
        private readonly PeerReviewCalculator $peerReviewCalculator,
        private readonly TrainingCompletionCalculator $trainingCompletionCalculator,
        private readonly ScoreRepository $scoreRepository,
        private readonly ReportService $reportService,
        private readonly BusinessRule $businessRule
    ) {
        $this->weights = config('performance.weights', [
            'task_completion' => 0.30,
            'deadline_adherence' => 0.25,
            'peer_reviews' => 0.25,
            'training_completion' => 0.20,
        ]);
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
        $latest = $this->scoreRepository->getLatest($employee);
        if ($latest) {
            $snapshot = $this->snapshotBuilder->build(
                $employee,
                $latest->task_completion,
                $latest->deadline_adherence,
                $latest->peer_reviews,
                $latest->training_completion,
                $latest->final_score,
                $latest->department_rank,
                $latest
            );
        } else {
            $snapshot = [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department?->name,
                'performance_score' => 0.0,
                'breakdown' => [
                    'task_completion' => 0.0,
                    'deadline_adherence' => 0.0,
                    'peer_reviews' => 0.0,
                    'training_completion' => 0.0,
                ],
                'department_rank' => null,
                'total_employees_in_department' => $employee->department?->users()->count() ?? 0,
                'last_calculated' => null,
            ];
        }

        $report = $this->reportService->buildEmployeeReport($employee, $from, $to);

        return [
            'snapshot' => $snapshot,
            'trend' => $report['trend'],
            'components_trend' => $report['components_trend'],
            'department' => $report['department'],
            'weights' => [
                'task_completion' => ($this->weights['task_completion'] * 100),
                'deadline_adherence' => ($this->weights['deadline_adherence'] * 100),
                'peer_reviews' => ($this->weights['peer_reviews'] * 100),
                'training_completion' => ($this->weights['training_completion'] * 100),
            ],
        ];
    }

    private function weightedScore(float $taskCompletion, float $deadlineAdherence, float $peerReview, float $trainingCompletion): float
    {
        return $taskCompletion * $this->weights['task_completion'] +
            $deadlineAdherence * $this->weights['deadline_adherence'] +
            $peerReview * $this->weights['peer_reviews'] +
            $trainingCompletion * $this->weights['training_completion'];
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
        $finalScore = $this->businessRule->apply($employee, $finalScoreRaw);
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

    private function calculateDepartmentRank(User $employee, ?float $employeeScore = null): ?int
    {
        if (!$employee->department) {
            return null;
        }

        if ($employeeScore === null) {
            $latestSelf = $employee->performanceScores()->orderByDesc('calculated_at')->first();
            $employeeScore = $latestSelf?->final_score ?? 0.0;
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
