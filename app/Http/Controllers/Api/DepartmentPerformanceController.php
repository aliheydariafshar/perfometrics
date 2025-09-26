<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\PerformanceCalculator;

class DepartmentPerformanceController extends Controller
{
    public function __construct(private readonly PerformanceCalculator $calculator)
    {

    }

    
    public function __invoke(int $id)
    {
        $department = Department::query()->findOrFail($id);

        $summary = $this->calculator->departmentSummary($department);
        return response()->json($summary);
    }
}
