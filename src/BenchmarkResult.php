<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Result of a benchmark comparison across multiple callables.
 */
final readonly class BenchmarkResult
{
    /**
     * Create a new BenchmarkResult instance.
     *
     * @param  array<int, BenchmarkEntry>  $entries
     */
    public function __construct(
        private array $entries,
    ) {}

    /**
     * Get the benchmark entries keyed by callable index.
     *
     * @return array<int, BenchmarkEntry>
     */
    public function results(): array
    {
        return $this->entries;
    }

    /**
     * Generate a formatted report comparing all callables.
     */
    public function report(): string
    {
        $lines = [];
        $lines[] = 'Benchmark Results';
        $lines[] = str_repeat('-', 18);

        foreach ($this->entries as $index => $entry) {
            $lines[] = sprintf(
                '#%d — mean: %s | median: %s | min: %s | max: %s (%d iterations)',
                $index,
                $entry->meanFormatted,
                $entry->medianFormatted,
                $entry->minFormatted,
                $entry->maxFormatted,
                $entry->iterations,
            );
        }

        return implode("\n", $lines);
    }
}
