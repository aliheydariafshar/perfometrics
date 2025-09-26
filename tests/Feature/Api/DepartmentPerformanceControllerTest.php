<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DepartmentPerformanceControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_ok(): void
    {
        $dept = Department::factory()->create();
        $this->assertDatabaseHas('departments', ['id' => $dept->id]);
        $this->withoutExceptionHandling();
        $response = $this->getJson(route('departments.performance.summary', ['id' => $dept->id]));
        $response->assertOk();
        $response->assertJsonStructure(['department_id', 'department_name', 'average_score', 'total_employees', 'top_performers', 'improvement_needed']);
    }

    public function test_not_found(): void
    {
        $response = $this->getJson(route('departments.performance.summary', ['id' => 999999]));
        $response->assertNotFound();
    }
}
