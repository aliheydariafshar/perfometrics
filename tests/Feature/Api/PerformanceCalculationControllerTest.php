<?php

namespace Tests\Feature\Api;

use App\Jobs\OrchestratePerformanceBatchJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PerformanceCalculationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_kickoff_dispatches_and_returns_202(): void
    {
        Queue::fake();
        $response = $this->postJson(route('performance.calculate-all'));
        $response->assertStatus(202);
        $response->assertHeader('X-Batch-Id');
        Queue::assertPushed(OrchestratePerformanceBatchJob::class);
    }
}
