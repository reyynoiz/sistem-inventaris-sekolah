<?php

namespace Tests\Unit;

use App\Services\ReputationScorer;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReputationScorerTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanggal dikunci supaya hasil hitungan selalu sama
        $this->now = Carbon::parse('2026-11-18 00:00:00');
    }

    private function event(string $kind, int $daysAgo = 0): array
    {
        return ['kind' => $kind, 'at' => $this->now->copy()->subDays($daysAgo)];
    }

    public function test_new_user_starts_neutral(): void
    {
        $result = (new ReputationScorer)->compute([], $this->now);

        $this->assertSame(1.0, $result['alpha']);
        $this->assertSame(1.0, $result['beta']);
        $this->assertEqualsWithDelta(0.5, $result['score'], 0.0001);
    }

    public function test_one_good_return_raises_score(): void
    {
        $result = (new ReputationScorer)->compute([$this->event('good')], $this->now);

        $this->assertEqualsWithDelta(2 / 3, $result['score'], 0.0001);
    }

    public function test_ten_good_returns_and_two_heavy_lates(): void
    {
        $events = array_merge(
            array_fill(0, 10, $this->event('good')),
            array_fill(0, 2, $this->event('late_heavy')),
        );

        $result = (new ReputationScorer)->compute($events, $this->now);

        $this->assertEqualsWithDelta(11.0, $result['alpha'], 0.0001);
        $this->assertEqualsWithDelta(3.0, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(11 / 14, $result['score'], 0.0001);
    }

    public function test_light_late_weighs_half(): void
    {
        $result = (new ReputationScorer)->compute([$this->event('late_light')], $this->now);

        $this->assertEqualsWithDelta(1.5, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(0.4, $result['score'], 0.0001);
    }

    public function test_negligent_damage_weighs_two(): void
    {
        $result = (new ReputationScorer)->compute([$this->event('damage_negligent')], $this->now);

        $this->assertEqualsWithDelta(3.0, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(0.25, $result['score'], 0.0001);
    }

    public function test_wear_damage_has_no_effect(): void
    {
        $result = (new ReputationScorer)->compute([$this->event('damage_wear')], $this->now);

        $this->assertEqualsWithDelta(1.0, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(0.5, $result['score'], 0.0001);
    }

    public function test_violation_halves_after_one_half_life(): void
    {
        $result = (new ReputationScorer(halfLifeDays: 90))
            ->compute([$this->event('late_heavy', 90)], $this->now);

        $this->assertEqualsWithDelta(1.5, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(0.4, $result['score'], 0.0001);
    }

    public function test_violation_quarter_after_two_half_lives(): void
    {
        $result = (new ReputationScorer(halfLifeDays: 90))
            ->compute([$this->event('late_heavy', 180)], $this->now);

        $this->assertEqualsWithDelta(1.25, $result['beta'], 0.0001);
        $this->assertEqualsWithDelta(1 / 2.25, $result['score'], 0.0001);
    }

    public function test_good_return_decays_by_default(): void
    {
        $result = (new ReputationScorer(halfLifeDays: 90))
            ->compute([$this->event('good', 90)], $this->now);

        $this->assertEqualsWithDelta(1.5, $result['alpha'], 0.0001);
        $this->assertEqualsWithDelta(0.6, $result['score'], 0.0001);
    }

    public function test_good_return_does_not_decay_when_disabled(): void
    {
        $result = (new ReputationScorer(halfLifeDays: 90, decayPositive: false))
            ->compute([$this->event('good', 90)], $this->now);

        $this->assertEqualsWithDelta(2.0, $result['alpha'], 0.0001);
        $this->assertEqualsWithDelta(2 / 3, $result['score'], 0.0001);
    }

    public function test_future_event_is_not_discounted(): void
    {
        // Kejadian bertanggal besok dianggap berumur 0 hari, bukan lebih berat dari 1
        $result = (new ReputationScorer)->compute([$this->event('late_heavy', -1)], $this->now);

        $this->assertEqualsWithDelta(2.0, $result['beta'], 0.0001);
    }

    public function test_worked_example_in_proposal(): void
    {
        $events = [
            $this->event('good', 100),
            $this->event('good', 70),
            $this->event('good', 40),
            $this->event('good', 10),
            $this->event('late_heavy', 20),
        ];

        $result = (new ReputationScorer(halfLifeDays: 90))->compute($events, $this->now);

        $this->assertEqualsWithDelta(3.707, $result['alpha'], 0.001);
        $this->assertEqualsWithDelta(1.857, $result['beta'], 0.001);
        $this->assertEqualsWithDelta(0.666, $result['score'], 0.001);
    }

    public function test_tier_boundaries(): void
    {
        $scorer = new ReputationScorer;

        $this->assertSame(['name' => 'terpercaya', 'limit' => 5], $scorer->tier(1.0));
        $this->assertSame(['name' => 'terpercaya', 'limit' => 5], $scorer->tier(0.70));
        $this->assertSame(['name' => 'standar', 'limit' => 3], $scorer->tier(0.699));
        $this->assertSame(['name' => 'standar', 'limit' => 3], $scorer->tier(0.5));
        $this->assertSame(['name' => 'standar', 'limit' => 3], $scorer->tier(0.40));
        $this->assertSame(['name' => 'terbatas', 'limit' => 1], $scorer->tier(0.399));
        $this->assertSame(['name' => 'terbatas', 'limit' => 1], $scorer->tier(0.0));
    }

    public function test_unknown_kind_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ReputationScorer)->compute([$this->event('selfie')], $this->now);
    }

    public function test_invalid_half_life_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ReputationScorer(halfLifeDays: 0);
    }
}
