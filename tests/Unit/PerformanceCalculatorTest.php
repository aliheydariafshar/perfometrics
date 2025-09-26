<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\User;
use App\Services\BusinessRules\BusinessRule;
use App\Services\DeadlineAdherenceCalculator;
use App\Services\PeerReviewCalculator;
use App\Services\PerformanceCalculator;
use App\Services\ReportService;
use App\Services\ScoreRepository;
use App\Services\SnapshotBuilder;
use App\Services\TaskCompletionCalculator;
use App\Services\TrainingCompletionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_config_weights_and_business_rule(): void
    {
        config()->set('performance.weights', [
            'task_completion' => 0.50,
            'deadline_adherence' => 0.20,
            'peer_reviews' => 0.20,
            'training_completion' => 0.10,
        ]);

        // Stubs returning fixed component scores (0..100)
        $task = new class extends TaskCompletionCalculator {
            public function calculate(User $employee): float { return 80.0; }
        };
        $deadline = new class extends DeadlineAdherenceCalculator {
            public function calculate(User $employee): float { return 70.0; }
        };
        $peer = new class extends PeerReviewCalculator {
            public function calculate(User $employee): float { return 60.0; }
        };
        $training = new class extends TrainingCompletionCalculator {
            public function calculate(User $employee): float { return 90.0; }
        };

        // Business rule that adds +5 to the final score
        $rule = new class implements BusinessRule {
            public function apply(User $employee, float $currentScore): float { return $currentScore + 5.0; }
        };

        $calc = new PerformanceCalculator(
            new SnapshotBuilder(),
            $task,
            $deadline,
            $peer,
            $training,
            app(ScoreRepository::class),
            app(ReportService::class),
            $rule
        );

        $dept = Department::factory()->create();
        $user = User::factory()->create(['department_id' => $dept->id]);

        $snapshot = $calc->calculateForEmployee($user);

        // Weighted: 80*0.5 + 70*0.2 + 60*0.2 + 90*0.1 = 40 + 14 + 12 + 9 = 75; +5 rule => 80
        $this->assertSame(80.0, $snapshot['performance_score']);
    }
}


