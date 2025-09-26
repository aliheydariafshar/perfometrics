<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $department_id
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Department $department
 * @property Collection|ProjectMilestone[] $projectMilestones
 * @property Collection|Task[] $tasks
 *
 * @package App\Models
 */
class Project extends Model
{
    use HasFactory;
    protected $table = 'projects';

    protected $casts = [
        'department_id' => 'int',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'start_date',
        'end_date'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function projectMilestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
