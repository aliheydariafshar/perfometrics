<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\OrchestratePerformanceBatchJob;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use Throwable;

class PerformanceCalculationController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(): Response
    {
        $batch = Bus::batch([])->name('calculate-all-performance')->dispatch();

        OrchestratePerformanceBatchJob::dispatch($batch->id);

        return response()->noContent(202)->header('X-Batch-Id', $batch->id);
    }
}
