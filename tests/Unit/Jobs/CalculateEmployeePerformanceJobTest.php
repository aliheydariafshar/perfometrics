<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CalculateEmployeePerformanceJob;
use App\Models\User;
use App\Services\PerformanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CalculateEmployeePerformanceJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_calls_calculator_when_user_exists(): void
    {
        $user = User::factory()->create();

        $calculator = Mockery::mock(PerformanceCalculator::class);
        $calculator->shouldReceive('calculateForEmployee')->once();

        $job = new CalculateEmployeePerformanceJob($user->id);
        $job->handle($calculator);
        $this->assertTrue(true);
    }

    public function test_handle_noop_when_user_missing(): void
    {
        $calculator = Mockery::mock(PerformanceCalculator::class);
        $calculator->shouldReceive('calculateForEmployee')->never();

        $job = new CalculateEmployeePerformanceJob(999999);
        $job->handle($calculator);
        $this->assertTrue(true);
    }
}
