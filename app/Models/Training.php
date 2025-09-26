<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Training
 *
 * @property int $id
 * @property string $title
 * @property bool $required
 * @property int $year
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|EmployeeTraining[] $employeeTrainings
 *
 * @package App\Models
 */
class Training extends Model
{
    use HasFactory;
    protected $table = 'trainings';

    protected $casts = [
        'required' => 'bool',
        'year' => 'int'
    ];

    protected $fillable = [
        'title',
        'required',
        'year'
    ];

    public function employeeTrainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }
}
