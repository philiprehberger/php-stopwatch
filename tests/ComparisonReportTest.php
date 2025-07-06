<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\BenchmarkEntry;
use PhilipRehberger\Stopwatch\ComparisonReport;
use PhilipRehberger\Stopwatch\Stopwatch;
use PHPUnit\Framework\TestCase;

final class ComparisonReportTest extends TestCase
{
    public function test_compare_returns_comparison_report(): void
    {
        $report = Stopwatch::compare([
            'fast' => fn () => usleep(100),
            'slow' => fn () => usleep(500),
        ], iterations: 5);

        $this->assertInstanceOf(ComparisonReport::class, $report);
    }

    public function test_to_table_contains_headers_and_entries(): void
    {
        $report = Stopwatch::compare([
            'array_map' => fn () => array_map(fn ($x) => $x * 2, range(1, 100)),
            'foreach' => function () {
                $arr = range(1, 100);
                foreach ($arr as &$x) {
                    $x *= 2;
                }
            },
        ], iterations: 10);

        $table = $report->toTable();

        $this->assertStringContainsString('Name', $table);
        $this->assertStringContainsString('Mean', $table);
        $this->assertStringContainsString('Median', $table);
        $this->assertStringContainsString('vs Best', $table);
        $this->assertStringContainsString('array_map', $table);
        $this->assertStringContainsString('foreach', $table);
        $this->assertStringContainsString('baseline', $table);
    }

    public function test_fastest_returns_entry_with_lowest_mean(): void
    {
        $entries = [
            'slow' => new BenchmarkEntry(mean: 10.0, median: 9.0, min: 5.0, max: 15.0, iterations: 100),
            'fast' => new BenchmarkEntry(mean: 2.0, median: 1.8, min: 1.0, max: 3.0, iterations: 100),
            'medium' => new BenchmarkEntry(mean: 5.0, median: 4.5, min: 3.0, max: 7.0, iterations: 100),
        ];

        $report = new ComparisonReport($entries);

        $this->assertSame(2.0, $report->fastest()->mean);
    }

    public function test_slowest_returns_entry_with_highest_mean(): void
    {
        $entries = [
            'slow' => new BenchmarkEntry(mean: 10.0, median: 9.0, min: 5.0, max: 15.0, iterations: 100),
            'fast' => new BenchmarkEntry(mean: 2.0, median: 1.8, min: 1.0, max: 3.0, iterations: 100),
            'medium' => new BenchmarkEntry(mean: 5.0, median: 4.5, min: 3.0, max: 7.0, iterations: 100),
        ];

        $report = new ComparisonReport($entries);

        $this->assertSame(10.0, $report->slowest()->mean);
    }

    public function test_rankings_sorted_by_mean_ascending(): void
    {
        $entries = [
            'slow' => new BenchmarkEntry(mean: 10.0, median: 9.0, min: 5.0, max: 15.0, iterations: 100),
            'fast' => new BenchmarkEntry(mean: 2.0, median: 1.8, min: 1.0, max: 3.0, iterations: 100),
            'medium' => new BenchmarkEntry(mean: 5.0, median: 4.5, min: 3.0, max: 7.0, iterations: 100),
        ];

        $report = new ComparisonReport($entries);
        $rankings = $report->rankings();

        $names = array_keys($rankings);

        $this->assertSame(['fast', 'medium', 'slow'], $names);
    }

    public function test_to_array_returns_original_entries(): void
    {
        $entries = [
            'a' => new BenchmarkEntry(mean: 1.0, median: 1.0, min: 0.5, max: 1.5, iterations: 10),
            'b' => new BenchmarkEntry(mean: 2.0, median: 2.0, min: 1.0, max: 3.0, iterations: 10),
        ];

        $report = new ComparisonReport($entries);

        $this->assertSame($entries, $report->toArray());
    }

    public function test_to_table_has_separator_line(): void
    {
        $entries = [
            'test' => new BenchmarkEntry(mean: 1.0, median: 1.0, min: 0.5, max: 1.5, iterations: 10),
        ];

        $report = new ComparisonReport($entries);
        $table = $report->toTable();

        $lines = explode("\n", $table);

        // Second line should be all dashes and pipes
        $this->assertMatchesRegularExpression('/^\|[-]+\|[-]+\|[-]+\|[-]+\|$/', $lines[1]);
    }

    public function test_compare_with_default_iterations(): void
    {
        $report = Stopwatch::compare([
            'noop' => fn () => null,
        ]);

        $entries = $report->toArray();
        $entry = reset($entries);

        $this->assertSame(100, $entry->iterations);
    }

    public function test_to_table_shows_delta_percentage(): void
    {
        $entries = [
            'fast' => new BenchmarkEntry(mean: 1.0, median: 1.0, min: 0.5, max: 1.5, iterations: 10),
            'slow' => new BenchmarkEntry(mean: 2.0, median: 2.0, min: 1.0, max: 3.0, iterations: 10),
        ];

        $report = new ComparisonReport($entries);
        $table = $report->toTable();

        $this->assertStringContainsString('+100.00%', $table);
    }
}
