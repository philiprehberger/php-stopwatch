<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Statistics for a single callable within a benchmark comparison.
 */
final readonly class BenchmarkEntry
{
    public string $meanFormatted;

    public string $medianFormatted;

    public string $minFormatted;

    public string $maxFormatted;

    /**
     * Create a new BenchmarkEntry instance.
     */
    public function __construct(
        public float $mean,
        public float $median,
        public float $min,
        public float $max,
        public int $iterations,
    ) {
        $this->meanFormatted = Formatter::formatDuration($this->mean);
        $this->medianFormatted = Formatter::formatDuration($this->median);
        $this->minFormatted = Formatter::formatDuration($this->min);
        $this->maxFormatted = Formatter::formatDuration($this->max);
    }
}
