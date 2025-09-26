<?php

namespace Tests\Unit\Jobs;

use App\Jobs\OrchestratePerformanceBatchJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class OrchestratePerformanceBatchJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_jobs_to_existing_batch(): void
    {
        User::factory()->count(3)->create();

        $batch = Bus::batch([])->name('test-batch')->dispatch();

        $job = new OrchestratePerformanceBatchJob($batch->id);
        $job->handle();

        $found = Bus::findBatch($batch->id);
        $this->assertNotNull($found);
        $this->assertTrue($found->totalJobs > 0);
    }

    public function test_noop_when_batch_missing(): void
    {
        $job = new OrchestratePerformanceBatchJob('non-existent-batch-id');
        $job->handle();
        $this->assertTrue(true);
    }
}
