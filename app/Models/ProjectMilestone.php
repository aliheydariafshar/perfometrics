<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProjectMilestone
 *
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Project $project
 *
 * @package App\Models
 */
class ProjectMilestone extends Model
{
    use HasFactory;
    protected $table = 'project_milestones';

    protected $casts = [
        'project_id' => 'int',
        'due_date' => 'datetime',
        'completed_date' => 'datetime'
    ];

    protected $fillable = [
        'project_id',
        'title',
        'due_date',
        'completed_date'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
