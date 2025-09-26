<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BatchStatusControllerTest extends TestCase
{
    public function test_not_found(): void
    {
        $response = $this->getJson(route('performance.batches.show', ['id' => 'nonexistent']));
        $response->assertNotFound();
    }

    public function test_ok_state(): void
    {
        // Create a batch
        $batch = Bus::batch([])->name('test-batch')->dispatch();

        $response = $this->getJson(route('performance.batches.show', ['id' => $batch->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'total_jobs',
                'pending_jobs',
                'failed_jobs',
                'processed_jobs',
                'progress',
                'cancelled',
                'created_at',
                'finished_at',
            ])
            ->assertJson([
                'id' => $batch->id,
                'name' => 'test-batch',
                'total_jobs' => 0,
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'processed_jobs' => 0,
                'progress' => 0,
                'cancelled' => false,
            ]);
    }
}
