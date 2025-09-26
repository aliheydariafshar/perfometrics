<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Task
 *
 * @property int $id
 * @property int $project_id
 * @property int $assignee_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property Project $project
 *
 * @package App\Models
 */
class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';

    protected $casts = [
        'project_id' => 'int',
        'assignee_id' => 'int',
        'due_date' => 'datetime',
        'completed_date' => 'datetime'
    ];

    protected $fillable = [
        'project_id',
        'assignee_id',
        'title',
        'description',
        'due_date',
        'completed_date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
