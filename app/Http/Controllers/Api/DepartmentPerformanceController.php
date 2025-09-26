<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\DepartmentAnalytics;

class DepartmentPerformanceController extends Controller
{
    public function __construct(private readonly DepartmentAnalytics $analytics)
    {

    }


    public function __invoke(int $id)
    {
        $department = Department::query()->findOrFail($id);

        $summary = $this->analytics->summary($department);
        return response()->json($summary);
    }
}
