## Perfometrics - Employee Performance Analytics

A Laravel-based employee performance analytics system that calculates and exposes performance metrics per employee and department, matching the assignment spec.

### Setup

1. Copy `.env.example` to `.env` and configure DB
2. `composer install`
3. `php artisan key:generate`
4. For SQLite quick start:
   - `touch database/database.sqlite`
   - `php artisan migrate --force`
5. Optional: seed sample data `php artisan db:seed`
6. Run the queue worker for batch jobs: `php artisan queue:work`
7. Serve API: `php artisan serve`

### API Endpoints (named and rate-limited)

- employees.performance.show → `GET /api/employees/{id}/performance`
- departments.performance.summary → `GET /api/departments/{id}/performance-summary`
- employees.performance.report → `GET /api/performance/reports/{employee_id}`
- performance.calculate-all (heavier limit) → `POST /api/performance/calculate-all`
- performance.batches.show → `GET /api/performance/batches/{id}`

Notes:
- Batch kickoff returns 202 with `X-Batch-Id`; progress available via batches.show
- Report validates dates and enforces `from <= to` (422 on error)

### Business Logic

- Performance Score weights are configurable in `config/performance.php` (defaults: 30/25/25/20)
- Business rules are pluggable via `App\Services\BusinessRules\BusinessRule`; default `NewEmployeeMinimumRule` uses config values (defaults: tenure < 3 months ⇒ min 50)
- Missing component data counts as 0; scores rounded to 1 decimal

### Architecture

- Calculators in `app/Services` (task, deadline, peer review, training)
- `PerformanceCalculator` applies weights and business rules; persistence and department analytics are handled by `ScoreRepository` and `DepartmentAnalytics`
- Batch orchestration via `OrchestratePerformanceBatchJob` adding `CalculateEmployeePerformanceJob` per user

### Rate Limiting

- `api-default`: 60 req/min for read endpoints
- `api-heavy`: 10 req/min for batch kickoff

### Testing

- Run: `php artisan test`
- Uses SQLite by default (ensure `database/database.sqlite` exists)
- Unit tests for calculators; feature tests per controller with success and error cases
 - `PerformanceCalculatorTest` verifies config-driven weights and rule application

### Assumptions

- Peer review scores are 1–10 and scaled ×10
- Only current-year required trainings count
- Department rank computed against latest scores in department
