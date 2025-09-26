<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;

class BatchStatusController extends Controller
{
    public function __invoke(string $id): JsonResponse
    {
        $batch = Bus::findBatch($id);
        if (! $batch) {
            return response()->json(['message' => 'Batch not found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'processed_jobs' => $batch->processedJobs(),
            'progress' => $batch->progress(),
            'cancelled' => $batch->cancelled(),
            'created_at' => $batch->createdAt?->toIso8601String(),
            'finished_at' => $batch->finishedAt?->toIso8601String(),
        ]);
    }
}
