<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class OrchestratePerformanceBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The ID of the batch to populate.
     */
    protected string $batchId;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        /** @var Batch|null $batch */
        $batch = Bus::findBatch($this->batchId);
        if (! $batch) {
            return;
        }

        User::query()->select('id')->orderBy('id')->chunkById(500, function ($users) use ($batch) {
            $jobs = [];
            foreach ($users as $user) {
                $jobs[] = new CalculateEmployeePerformanceJob($user->id);
            }

            if (! empty($jobs)) {
                $batch->add($jobs);
            }
        });
    }
}
