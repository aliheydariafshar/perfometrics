<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePerformanceShowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ok(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('employees.performance.show', ['id' => $user->id]));
        $response->assertOk();
        $response->assertJsonStructure([
            'employee_id', 'name', 'department', 'performance_score', 'breakdown' => [
                'task_completion', 'deadline_adherence', 'peer_reviews', 'training_completion'
            ]
        ]);
    }

    public function test_not_found(): void
    {
        $response = $this->getJson(route('employees.performance.show', ['id' => 999999]));
        $response->assertNotFound();
    }
}
