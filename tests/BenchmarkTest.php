<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\BenchmarkEntry;
use PhilipRehberger\Stopwatch\BenchmarkResult;
use PhilipRehberger\Stopwatch\Stopwatch;
use PHPUnit\Framework\TestCase;

final class BenchmarkTest extends TestCase
{
    public function test_benchmark_returns_benchmark_result(): void
    {
        $result = Stopwatch::benchmark([
            fn () => usleep(100),
            fn () => usleep(200),
        ], iterations: 10);

        $this->assertInstanceOf(BenchmarkResult::class, $result);
    }

    public function test_benchmark_results_keyed_by_index(): void
    {
        $result = Stopwatch::benchmark([
            fn () => usleep(100),
            fn () => usleep(100),
            fn () => usleep(100),
        ], iterations: 5);

        $results = $result->results();

        $this->assertCount(3, $results);
        $this->assertArrayHasKey(0, $results);
        $this->assertArrayHasKey(1, $results);
        $this->assertArrayHasKey(2, $results);
    }

    public function test_benchmark_entry_has_valid_stats(): void
    {
        $result = Stopwatch::benchmark([
            fn () => usleep(500),
        ], iterations: 20);

        $entry = $result->results()[0];

        $this->assertInstanceOf(BenchmarkEntry::class, $entry);
        $this->assertGreaterThan(0, $entry->mean);
        $this->assertGreaterThan(0, $entry->median);
        $this->assertGreaterThan(0, $entry->min);
        $this->assertGreaterThan(0, $entry->max);
        $this->assertGreaterThanOrEqual($entry->min, $entry->mean);
        $this->assertLessThanOrEqual($entry->max, $entry->mean);
        $this->assertSame(20, $entry->iterations);
    }

    public function test_benchmark_entry_has_formatted_values(): void
    {
        $result = Stopwatch::benchmark([
            fn () => usleep(100),
        ], iterations: 5);

        $entry = $result->results()[0];

        $this->assertNotEmpty($entry->meanFormatted);
        $this->assertNotEmpty($entry->medianFormatted);
        $this->assertNotEmpty($entry->minFormatted);
        $this->assertNotEmpty($entry->maxFormatted);
    }

    public function test_benchmark_report_contains_all_entries(): void
    {
        $result = Stopwatch::benchmark([
            fn () => usleep(100),
            fn () => usleep(200),
        ], iterations: 5);

        $report = $result->report();

        $this->assertStringContainsString('Benchmark Results', $report);
        $this->assertStringContainsString('#0', $report);
        $this->assertStringContainsString('#1', $report);
        $this->assertStringContainsString('mean:', $report);
        $this->assertStringContainsString('median:', $report);
        $this->assertStringContainsString('min:', $report);
        $this->assertStringContainsString('max:', $report);
        $this->assertStringContainsString('5 iterations', $report);
    }

    public function test_benchmark_default_iterations(): void
    {
        $result = Stopwatch::benchmark([
            fn () => null,
        ]);

        $entry = $result->results()[0];

        $this->assertSame(100, $entry->iterations);
    }
}
