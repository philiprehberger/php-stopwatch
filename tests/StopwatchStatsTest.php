<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\Lap;
use PhilipRehberger\Stopwatch\StopwatchResult;
use PhilipRehberger\Stopwatch\StopwatchStats;
use PHPUnit\Framework\TestCase;

final class StopwatchStatsTest extends TestCase
{
    public function test_stats_with_known_values(): void
    {
        // Known durations: 10, 20, 30, 40, 50
        $stats = new StopwatchStats([10.0, 20.0, 30.0, 40.0, 50.0]);

        $this->assertSame(30.0, $stats->mean());
        $this->assertSame(30.0, $stats->median());
        $this->assertSame(10.0, $stats->min());
        $this->assertSame(50.0, $stats->max());
    }

    public function test_stats_mean_with_even_count(): void
    {
        $stats = new StopwatchStats([10.0, 20.0, 30.0, 40.0]);

        $this->assertSame(25.0, $stats->mean());
    }

    public function test_stats_median_with_even_count(): void
    {
        // Sorted: 10, 20, 30, 40 — median via interpolation at rank 1.5
        $stats = new StopwatchStats([10.0, 40.0, 20.0, 30.0]);

        $this->assertSame(25.0, $stats->median());
    }

    public function test_stats_single_value(): void
    {
        $stats = new StopwatchStats([42.0]);

        $this->assertSame(42.0, $stats->mean());
        $this->assertSame(42.0, $stats->median());
        $this->assertSame(42.0, $stats->min());
        $this->assertSame(42.0, $stats->max());
        $this->assertSame(42.0, $stats->p95());
        $this->assertSame(42.0, $stats->p99());
        $this->assertEqualsWithDelta(0.0, $stats->standardDeviation(), 0.001);
    }

    public function test_stats_standard_deviation(): void
    {
        // Values: 2, 4, 4, 4, 5, 5, 7, 9
        // Mean = 5, population std dev = 2.0
        $stats = new StopwatchStats([2.0, 4.0, 4.0, 4.0, 5.0, 5.0, 7.0, 9.0]);

        $this->assertEqualsWithDelta(5.0, $stats->mean(), 0.001);
        $this->assertEqualsWithDelta(2.0, $stats->standardDeviation(), 0.001);
    }

    public function test_stats_percentiles(): void
    {
        // 100 values from 1 to 100
        $durations = array_map(fn (int $i): float => (float) $i, range(1, 100));
        $stats = new StopwatchStats($durations);

        $this->assertEqualsWithDelta(50.5, $stats->mean(), 0.001);
        $this->assertEqualsWithDelta(50.5, $stats->median(), 0.001);
        $this->assertSame(1.0, $stats->min());
        $this->assertSame(100.0, $stats->max());
        $this->assertEqualsWithDelta(95.06, $stats->p95(), 0.1);
        $this->assertEqualsWithDelta(99.02, $stats->p99(), 0.1);
    }

    public function test_stats_from_stopwatch_result_with_laps(): void
    {
        $laps = [
            new Lap(name: 'a', duration: 10.0, cumulativeDuration: 10.0),
            new Lap(name: 'b', duration: 20.0, cumulativeDuration: 30.0),
            new Lap(name: 'c', duration: 30.0, cumulativeDuration: 60.0),
        ];

        $result = new StopwatchResult(
            duration: 60.0,
            memory: 0,
            peakMemory: 0,
            laps: $laps,
        );

        $stats = $result->stats();

        $this->assertInstanceOf(StopwatchStats::class, $stats);
        $this->assertEqualsWithDelta(20.0, $stats->mean(), 0.001);
        $this->assertSame(20.0, $stats->median());
        $this->assertSame(10.0, $stats->min());
        $this->assertSame(30.0, $stats->max());
    }

    public function test_stats_from_stopwatch_result_without_laps(): void
    {
        $result = new StopwatchResult(
            duration: 42.5,
            memory: 0,
            peakMemory: 0,
        );

        $stats = $result->stats();

        $this->assertSame(42.5, $stats->mean());
        $this->assertSame(42.5, $stats->median());
        $this->assertSame(42.5, $stats->min());
        $this->assertSame(42.5, $stats->max());
    }

    public function test_stats_accessor_methods_match_properties(): void
    {
        $stats = new StopwatchStats([5.0, 10.0, 15.0]);

        $this->assertSame($stats->mean, $stats->mean());
        $this->assertSame($stats->median, $stats->median());
        $this->assertSame($stats->min, $stats->min());
        $this->assertSame($stats->max, $stats->max());
        $this->assertSame($stats->p95, $stats->p95());
        $this->assertSame($stats->p99, $stats->p99());
        $this->assertSame($stats->standardDeviation, $stats->standardDeviation());
    }
}
