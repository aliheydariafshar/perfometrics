<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePerformanceReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ok(): void
    {
        $user = User::factory()->create();
        $response = $this->getJson(route('employees.performance.report', ['employee_id' => $user->id]));
        $response->assertOk();
        $response->assertJsonStructure(['snapshot', 'trend', 'components_trend', 'department', 'weights']);
    }

    public function test_not_found(): void
    {
        $response = $this->getJson(route('employees.performance.report', ['employee_id' => 999999]));
        $response->assertNotFound();
    }

    public function test_invalid_dates(): void
    {
        $user = User::factory()->create();
        $response = $this->getJson(route('employees.performance.report', ['employee_id' => $user->id]).'?from=invalid&to=2025-01-01');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['from']);
    }

    public function test_from_after_to(): void
    {
        $user = User::factory()->create();
        $response = $this->getJson(route('employees.performance.report', ['employee_id' => $user->id]).'?from=2025-02-01&to=2025-01-01');
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['to']);
    }
}
