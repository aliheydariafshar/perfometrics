<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PeerReview
 *
 * @property int $id
 * @property int $reviewee_id
 * @property int $reviewer_id
 * @property int $score
 * @property string|null $comments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 *
 * @package App\Models
 */
class PeerReview extends Model
{
    use HasFactory;
    protected $table = 'peer_reviews';

    protected $casts = [
        'reviewee_id' => 'int',
        'reviewer_id' => 'int',
        'score' => 'int'
    ];

    protected $fillable = [
        'reviewee_id',
        'reviewer_id',
        'score',
        'comments'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
