<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PerformanceCalculator;
use Illuminate\Http\JsonResponse;

class EmployeePerformanceShowController extends Controller
{
    public function __construct(private readonly PerformanceCalculator $calculator)
    {
    }

    public function __invoke(int $id): JsonResponse
    {
        $employee = User::with(['department'])->findOrFail($id);
        $result = $this->calculator->calculateForEmployee($employee);

        return response()->json($result);
    }
}
