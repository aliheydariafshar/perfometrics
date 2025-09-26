<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PerformanceCalculator;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateEmployeePerformanceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    public function __construct(private readonly int $userId)
    {
    }

    public function handle(PerformanceCalculator $calculator): void
    {
        $user = User::with(['department'])->find($this->userId);
        if (!$user) {
            return;
        }
        $calculator->calculateForEmployee($user);
    }
}
