<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\Lap;
use PhilipRehberger\Stopwatch\MeasureResult;
use PhilipRehberger\Stopwatch\StopwatchResult;
use PHPUnit\Framework\TestCase;

final class ValueObjectTest extends TestCase
{
    // ---------------------------------------------------------------
    // Lap
    // ---------------------------------------------------------------

    public function test_lap_stores_name_and_durations(): void
    {
        $lap = new Lap(name: 'setup', duration: 12.34, cumulativeDuration: 12.34);

        $this->assertSame('setup', $lap->name);
        $this->assertSame(12.34, $lap->duration);
        $this->assertSame(12.34, $lap->cumulativeDuration);
    }

    public function test_lap_allows_null_name(): void
    {
        $lap = new Lap(name: null, duration: 5.0, cumulativeDuration: 10.0);

        $this->assertNull($lap->name);
        $this->assertSame(5.0, $lap->duration);
        $this->assertSame(10.0, $lap->cumulativeDuration);
    }

    public function test_lap_with_zero_duration(): void
    {
        $lap = new Lap(name: 'instant', duration: 0.0, cumulativeDuration: 0.0);

        $this->assertSame(0.0, $lap->duration);
        $this->assertSame(0.0, $lap->cumulativeDuration);
    }

    public function test_lap_with_large_duration(): void
    {
        $lap = new Lap(name: 'long', duration: 999_999.99, cumulativeDuration: 1_500_000.0);

        $this->assertSame(999_999.99, $lap->duration);
        $this->assertSame(1_500_000.0, $lap->cumulativeDuration);
    }

    // ---------------------------------------------------------------
    // MeasureResult
    // ---------------------------------------------------------------

    public function test_measure_result_stores_duration_and_memory(): void
    {
        $result = new MeasureResult(duration: 42.5, memory: 2048);

        $this->assertSame(42.5, $result->duration);
        $this->assertSame(2048, $result->memory);
    }

    public function test_measure_result_formats_duration_automatically(): void
    {
        $result = new MeasureResult(duration: 150.0, memory: 1024);

        $this->assertSame('150.00ms', $result->durationFormatted);
    }

    public function test_measure_result_formats_memory_automatically(): void
    {
        $result = new MeasureResult(duration: 1.0, memory: 1024);

        $this->assertSame('1.00KB', $result->memoryFormatted);
    }

    public function test_measure_result_with_zero_values(): void
    {
        $result = new MeasureResult(duration: 0.0, memory: 0);

        $this->assertSame(0.0, $result->duration);
        $this->assertSame(0, $result->memory);
        $this->assertSame('0.00ms', $result->durationFormatted);
        $this->assertSame('0B', $result->memoryFormatted);
    }

    public function test_measure_result_with_negative_memory(): void
    {
        $result = new MeasureResult(duration: 5.0, memory: -512);

        $this->assertSame(-512, $result->memory);
        $this->assertSame('-512B', $result->memoryFormatted);
    }

    public function test_measure_result_with_seconds_range_duration(): void
    {
        $result = new MeasureResult(duration: 2500.0, memory: 0);

        $this->assertSame('2.50s', $result->durationFormatted);
    }

    public function test_measure_result_with_minutes_range_duration(): void
    {
        $result = new MeasureResult(duration: 120_000.0, memory: 0);

        $this->assertSame('2.00m', $result->durationFormatted);
    }

    // ---------------------------------------------------------------
    // StopwatchResult
    // ---------------------------------------------------------------

    public function test_stopwatch_result_stores_all_properties(): void
    {
        $laps = [
            new Lap(name: 'a', duration: 10.0, cumulativeDuration: 10.0),
            new Lap(name: 'b', duration: 15.0, cumulativeDuration: 25.0),
        ];

        $result = new StopwatchResult(
            duration: 30.0,
            memory: 4096,
            peakMemory: 8192,
            laps: $laps,
            name: 'test-run',
        );

        $this->assertSame(30.0, $result->duration);
        $this->assertSame(4096, $result->memory);
        $this->assertSame(8192, $result->peakMemory);
        $this->assertCount(2, $result->laps);
        $this->assertSame('test-run', $result->name);
    }

    public function test_stopwatch_result_defaults_to_empty_laps_and_null_name(): void
    {
        $result = new StopwatchResult(
            duration: 10.0,
            memory: 1024,
            peakMemory: 2048,
        );

        $this->assertSame([], $result->laps);
        $this->assertNull($result->name);
    }

    public function test_stopwatch_result_formats_duration_and_memory(): void
    {
        $result = new StopwatchResult(
            duration: 5500.0,
            memory: 1_048_576,
            peakMemory: 2_097_152,
        );

        $this->assertSame('5.50s', $result->durationFormatted);
        $this->assertSame('1.00MB', $result->memoryFormatted);
    }

    public function test_report_without_name(): void
    {
        $result = new StopwatchResult(
            duration: 100.0,
            memory: 512,
            peakMemory: 1024,
        );

        $report = $result->report();

        $this->assertStringContainsString('Stopwatch', $report);
        $this->assertStringNotContainsString('[', $report);
        $this->assertStringContainsString('Duration: 100.00ms', $report);
        $this->assertStringContainsString('Memory:   512B', $report);
        $this->assertStringContainsString('Peak:     1.00KB', $report);
        $this->assertStringNotContainsString('Laps:', $report);
    }

    public function test_report_with_name_and_laps(): void
    {
        $laps = [
            new Lap(name: 'init', duration: 20.0, cumulativeDuration: 20.0),
            new Lap(name: null, duration: 30.0, cumulativeDuration: 50.0),
        ];

        $result = new StopwatchResult(
            duration: 50.0,
            memory: 256,
            peakMemory: 512,
            laps: $laps,
            name: 'my-bench',
        );

        $report = $result->report();

        $this->assertStringContainsString('Stopwatch [my-bench]', $report);
        $this->assertStringContainsString('Laps:', $report);
        $this->assertStringContainsString('init', $report);
        // Unnamed lap should fall back to index-based label
        $this->assertStringContainsString('#2', $report);
    }

    public function test_report_unnamed_laps_use_one_based_index(): void
    {
        $laps = [
            new Lap(name: null, duration: 10.0, cumulativeDuration: 10.0),
            new Lap(name: null, duration: 10.0, cumulativeDuration: 20.0),
            new Lap(name: null, duration: 10.0, cumulativeDuration: 30.0),
        ];

        $result = new StopwatchResult(
            duration: 30.0,
            memory: 0,
            peakMemory: 0,
            laps: $laps,
        );

        $report = $result->report();

        $this->assertStringContainsString('#1', $report);
        $this->assertStringContainsString('#2', $report);
        $this->assertStringContainsString('#3', $report);
    }

    public function test_stopwatch_result_with_zero_values(): void
    {
        $result = new StopwatchResult(
            duration: 0.0,
            memory: 0,
            peakMemory: 0,
        );

        $this->assertSame('0.00ms', $result->durationFormatted);
        $this->assertSame('0B', $result->memoryFormatted);

        $report = $result->report();
        $this->assertStringContainsString('Duration: 0.00ms', $report);
        $this->assertStringContainsString('Memory:   0B', $report);
        $this->assertStringContainsString('Peak:     0B', $report);
    }
}
