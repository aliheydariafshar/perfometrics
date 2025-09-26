<?php

namespace App\Services;

use App\Models\Department;

class DepartmentAnalytics
{
    public function summary(Department $department): array
    {
        $totalEmployees = $department->users()->count();

        $sumScores = 0.0;
        $scoredCount = 0;
        $top = [];
        $bottom = [];

        $department->users()
            ->select(['id', 'name'])
            ->with('latestPerformanceScore')
            ->orderBy('id')
            ->chunkById(500, function ($users) use (&$sumScores, &$scoredCount, &$top, &$bottom) {
                foreach ($users as $u) {
                    $latestScore = $u->latestPerformanceScore?->final_score;

                    $score = $latestScore ?? 0.0;
                    $sumScores += $score;
                    $scoredCount++;

                    $entry = ['id' => $u->id, 'name' => $u->name, 'score' => $score];

                    // Maintain top 3
                    $top[] = $entry;
                    usort($top, fn ($a, $b) => $b['score'] <=> $a['score']);
                    if (count($top) > 3) {
                        array_pop($top);
                    }

                    // Maintain bottom 3
                    $bottom[] = $entry;
                    usort($bottom, fn ($a, $b) => $a['score'] <=> $b['score']);
                    if (count($bottom) > 3) {
                        array_pop($bottom);
                    }
                }
            });

        $average = $scoredCount > 0 ? $this->roundToOneDecimal($sumScores / $scoredCount) : 0.0;

        return [
            'department_id' => $department->id,
            'department_name' => $department->name,
            'average_score' => $average,
            'total_employees' => $totalEmployees,
            'top_performers' => $top,
            'improvement_needed' => $bottom,
        ];
    }

    private function roundToOneDecimal(float $value): float
    {
        $scaled = $value * 10;
        $rounded = $scaled >= 0 ? floor($scaled + 0.5) : ceil($scaled - 0.5);
        return (float) ($rounded / 10);
    }
}
