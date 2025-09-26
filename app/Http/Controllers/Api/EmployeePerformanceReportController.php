<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PerformanceCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class EmployeePerformanceReportController extends Controller
{
    public function __construct(private readonly PerformanceCalculator $calculator)
    {
    }

    public function __invoke(int $employeeId): JsonResponse
    {
        $employee = User::with(['department'])->findOrFail($employeeId);
        $validated = request()->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::createFromFormat('Y-m-d', $validated['from']) : null;
        $to = isset($validated['to']) ? Carbon::createFromFormat('Y-m-d', $validated['to']) : null;

        $report = $this->calculator->generateEmployeeReport($employee, $from, $to);
        return response()->json($report);
    }
}
