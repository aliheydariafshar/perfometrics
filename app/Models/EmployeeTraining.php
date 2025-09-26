<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EmployeeTraining
 *
 * @property int $id
 * @property int $user_id
 * @property int $training_id
 * @property Carbon|null $assigned_date
 * @property Carbon|null $completed_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Training $training
 * @property User $user
 *
 * @package App\Models
 */
class EmployeeTraining extends Model
{
    use HasFactory;
    protected $table = 'employee_training';

    protected $casts = [
        'user_id' => 'int',
        'training_id' => 'int',
        'assigned_date' => 'datetime',
        'completed_date' => 'datetime'
    ];

    protected $fillable = [
        'user_id',
        'training_id',
        'assigned_date',
        'completed_date'
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
