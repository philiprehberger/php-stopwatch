<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

use LogicException;

/**
 * Represents an actively running stopwatch that can record laps and be stopped.
 */
final class RunningStopwatch
{
    private float $startTime;

    private int $startMemory;

    private int $startPeakMemory;

    private bool $running = true;

    /** @var array<Lap> */
    private array $laps = [];

    private float $lastLapTime;

    /**
     * Create a new RunningStopwatch instance.
     *
     * @internal Use Stopwatch::start() to create instances.
     */
    public function __construct(
        private readonly ?string $name = null,
    ) {
        $this->startTime = hrtime(true) / 1e6;
        $this->startMemory = memory_get_usage();
        $this->startPeakMemory = memory_get_peak_usage();
        $this->lastLapTime = $this->startTime;
    }

    /**
     * Record a lap with an optional name.
     */
    public function lap(?string $name = null): self
    {
        $this->ensureRunning();

        $now = hrtime(true) / 1e6;
        $lapDuration = $now - $this->lastLapTime;
        $cumulativeDuration = $now - $this->startTime;

        $this->laps[] = new Lap(
            name: $name,
            duration: $lapDuration,
            cumulativeDuration: $cumulativeDuration,
        );

        $this->lastLapTime = $now;

        return $this;
    }

    /**
     * Stop the stopwatch and return the result.
     *
     * @throws LogicException If the stopwatch has already been stopped.
     */
    public function stop(): StopwatchResult
    {
        $this->ensureRunning();

        $endTime = hrtime(true) / 1e6;
        $endMemory = memory_get_usage();
        $endPeakMemory = memory_get_peak_usage();

        $this->running = false;

        return new StopwatchResult(
            duration: $endTime - $this->startTime,
            memory: $endMemory - $this->startMemory,
            peakMemory: $endPeakMemory - $this->startPeakMemory,
            laps: $this->laps,
            name: $this->name,
        );
    }

    /**
     * Get the elapsed time in milliseconds since the stopwatch was started.
     */
    public function elapsed(): float
    {
        $this->ensureRunning();

        return (hrtime(true) / 1e6) - $this->startTime;
    }

    /**
     * Check whether the stopwatch is still running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Ensure the stopwatch is still running.
     *
     * @throws LogicException If the stopwatch has been stopped.
     */
    private function ensureRunning(): void
    {
        if (! $this->running) {
            throw new LogicException('Stopwatch has already been stopped.');
        }
    }
}
