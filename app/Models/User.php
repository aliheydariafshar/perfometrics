<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class User
 *
 * @property int $id
 * @property int|null $department_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property Carbon|null $hire_date
 * @property string $job_title
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Department|null $department
 * @property Collection|EmployeeTraining[] $employeeTrainings
 * @property Collection|Training[] $trainings
 * @property Collection|PeerReview[] $peerReviews
 * @property Collection|PerformanceScore[] $performanceScores
 * @property Collection|Task[] $tasks
 *
 * @package App\Models
 */
class User extends Model
{
    use HasFactory;
    protected $table = 'users';

    protected $casts = [
        'department_id' => 'int',
        'email_verified_at' => 'datetime',
        'hire_date' => 'datetime'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'department_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'hire_date',
        'job_title',
        'remember_token'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeTrainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'employee_training')
            ->withPivot(['assigned_date', 'completed_date'])
            ->withTimestamps();
    }

    public function peerReviews(): HasMany
    {
        return $this->hasMany(PeerReview::class, 'reviewer_id');
    }

    public function peerReviewsReceived(): HasMany
    {
        return $this->hasMany(PeerReview::class, 'reviewee_id');
    }

    public function performanceScores(): HasMany
    {
        return $this->hasMany(PerformanceScore::class);
    }

    public function latestPerformanceScore(): HasOne
    {
        return $this->hasOne(PerformanceScore::class)->latestOfMany('calculated_at');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }
}
