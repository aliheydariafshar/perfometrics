<?php

namespace Tests\Unit;

use App\Models\PeerReview;
use App\Models\User;
use App\Services\PeerReviewCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeerReviewCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_min_reviews_then_scales_to_1000(): void
    {
        $user = User::factory()->create();
        // Below minimum
        PeerReview::factory()->count(2)->create([
            'reviewee_id' => $user->id,
            'score' => 8, // on ten scale
        ]);

        $calc = new PeerReviewCalculator();
        $this->assertSame(0.0, $calc->calculate($user));

        // Meet minimum, expect 8 * 10 = 80
        PeerReview::factory()->create([
            'reviewee_id' => $user->id,
            'score' => 8,
        ]);
        $this->assertSame(80.0, $calc->calculate($user));
    }
}
