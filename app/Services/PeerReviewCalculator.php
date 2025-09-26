<?php

namespace App\Services;

use App\Models\User;

class PeerReviewCalculator
{
    public const MIN_REVIEWS = 3;

    public function calculate(User $employee): float
    {
        $stats = $employee->peerReviewsReceived()
            ->selectRaw('COUNT(*) as review_count, AVG(score) as avg_score')
            ->first();

        $count = (int) ($stats->review_count ?? 0);
        if ($count < self::MIN_REVIEWS) {
            return 0.0;
        }

        $avgScoreTenScale = (float) ($stats->avg_score ?? 0.0);
        return $avgScoreTenScale * 10.0;
    }
}
