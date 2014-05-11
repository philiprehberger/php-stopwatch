<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Immutable result from a single callable measurement.
 */
final readonly class MeasureResult
{
    /**
     * Human-readable duration string.
     */
    public string $durationFormatted;

    /**
     * Human-readable memory delta string.
     */
    public string $memoryFormatted;

    /**
     * Create a new MeasureResult instance.
     */
    public function __construct(
        public float $duration,
        public int $memory,
    ) {
        $this->durationFormatted = Formatter::formatDuration($this->duration);
        $this->memoryFormatted = Formatter::formatBytes($this->memory);
    }
}
