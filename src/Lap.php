<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Represents a single lap recorded during stopwatch execution.
 */
final readonly class Lap
{
    /**
     * Create a new Lap instance.
     */
    public function __construct(
        public ?string $name,
        public float $duration,
        public float $cumulativeDuration,
    ) {}
}
