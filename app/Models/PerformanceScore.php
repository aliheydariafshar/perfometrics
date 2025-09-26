<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PerformanceScore
 *
 * @property int $id
 * @property int $user_id
 * @property float $task_completion
 * @property float $deadline_adherence
 * @property float $peer_reviews
 * @property float $training_completion
 * @property float $final_score
 * @property int|null $department_rank
 * @property Carbon|null $calculated_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 *
 * @package App\Models
 */
class PerformanceScore extends Model
{
    use HasFactory;
    protected $table = 'performance_scores';

    protected $casts = [
        'user_id' => 'int',
        'task_completion' => 'float',
        'deadline_adherence' => 'float',
        'peer_reviews' => 'float',
        'training_completion' => 'float',
        'final_score' => 'float',
        'department_rank' => 'int',
        'calculated_at' => 'datetime'
    ];

    protected $fillable = [
        'user_id',
        'task_completion',
        'deadline_adherence',
        'peer_reviews',
        'training_completion',
        'final_score',
        'department_rank',
        'calculated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
