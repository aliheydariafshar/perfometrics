<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Task;
use App\Models\User;
use App\Services\DeadlineAdherenceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineAdherenceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_percentage_of_met_milestones(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Task::factory()->create([
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_date' => now()->toDateString(),
            'completed_date' => now()->toDateString(),
        ]);

        ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'due_date' => now()->toDateString(),
            'completed_date' => now()->toDateString(),
        ]);
        ProjectMilestone::factory()->create([
            'project_id' => $project->id,
            'due_date' => now()->toDateString(),
            'completed_date' => null,
        ]);

        $calc = new DeadlineAdherenceCalculator();
        $percent = $calc->calculate($user);

        $percent = (float) number_format($percent, 1, '.', '');

        $this->assertSame(50.0, $percent);
    }
}
