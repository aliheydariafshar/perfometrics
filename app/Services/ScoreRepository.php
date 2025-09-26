<?php

namespace App\Services;

use App\Models\PerformanceScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ScoreRepository
{
    public function upsertLatest(
        User $employee,
        float $taskCompletion,
        float $deadlineAdherence,
        float $peerReview,
        float $trainingCompletion,
        float $finalScore,
        ?int $departmentRank
    ): PerformanceScore {
        return PerformanceScore::query()->updateOrCreate(
            ['user_id' => $employee->id],
            [
                'task_completion' => $taskCompletion,
                'deadline_adherence' => $deadlineAdherence,
                'peer_reviews' => $peerReview,
                'training_completion' => $trainingCompletion,
                'final_score' => $finalScore,
                'department_rank' => $departmentRank,
                'calculated_at' => Carbon::now(),
            ]
        );
    }

    public function historyQuery(User $employee): Builder
    {
        return PerformanceScore::query()
            ->where('user_id', $employee->id)
            ->orderBy('calculated_at');
    }

    public function getLatest(User $employee): ?PerformanceScore
    {
        return PerformanceScore::query()
            ->where('user_id', $employee->id)
            ->orderByDesc('calculated_at')
            ->first();
    }
}
