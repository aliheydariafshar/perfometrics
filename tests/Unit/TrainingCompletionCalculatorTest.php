<?php

namespace Tests\Unit;

use App\Models\Training;
use App\Models\User;
use App\Services\TrainingCompletionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingCompletionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_required_training_completion_for_current_year(): void
    {
        $user = User::factory()->create();

        $currentYear = now()->year;

        $t1 = Training::factory()->create(['required' => true, 'year' => $currentYear]);
        $user->trainings()->attach($t1->id, [
            'assigned_date' => now()->subMonth()->toDateString(),
            'completed_date' => now()->subWeek()->toDateString(),
        ]);

        $t2 = Training::factory()->create(['required' => true, 'year' => $currentYear]);
        $user->trainings()->attach($t2->id, [
            'assigned_date' => now()->subMonth()->toDateString(),
            'completed_date' => null,
        ]);

        $t3 = Training::factory()->create(['required' => false, 'year' => $currentYear]);
        $user->trainings()->attach($t3->id, [
            'assigned_date' => now()->subMonth()->toDateString(),
            'completed_date' => now()->toDateString(),
        ]);

        $calc = new TrainingCompletionCalculator();
        $percent = $calc->calculate($user);

        $percent = (float) number_format($percent, 1, '.', '');

        $this->assertSame(50.0, $percent);
    }
}
