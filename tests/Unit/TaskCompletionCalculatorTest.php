<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskCompletionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCompletionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_on_time_and_late_half_credit(): void
    {
        $user = User::factory()->create();

        // On-time
        Task::factory()->create([
            'assignee_id' => $user->id,
            'due_date' => now()->addDay()->toDateString(),
            'completed_date' => now()->toDateString(),
        ]);
        // Late (half credit)
        Task::factory()->create([
            'assignee_id' => $user->id,
            'due_date' => now()->subDay()->toDateString(),
            'completed_date' => now()->toDateString(),
        ]);
        // With due date but not completed
        Task::factory()->create([
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(2)->toDateString(),
            'completed_date' => null,
        ]);

        $calc = new TaskCompletionCalculator();
        $percent = $calc->calculate($user);

        // (1 on-time + 0.5 late) / 3 with due dates = 0.5 * 100 = 50
        $percent = (float) number_format($percent, 1, '.', '');

        $this->assertSame(50.0, $percent);
    }
}
