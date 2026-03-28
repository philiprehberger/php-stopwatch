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

    private ?StopwatchResult $lastResult = null;

    private bool $paused = false;

    private float $pausedAt = 0.0;

    private float $accumulatedPauseTime = 0.0;

    /** @var array<Lap> */
    private array $laps = [];

    /** @var array<RunningStopwatch> */
    private array $children = [];

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
     * Create a child stopwatch for nested timing.
     */
    public function child(string $name): self
    {
        $this->ensureRunning();
        $this->ensureNotPaused();

        $child = new self($name);
        $this->children[] = $child;

        return $child;
    }

    /**
     * Record a lap with an optional name.
     */
    public function lap(?string $name = null): self
    {
        $this->ensureRunning();
        $this->ensureNotPaused();

        $now = hrtime(true) / 1e6;
        $lapDuration = ($now - $this->lastLapTime) - $this->accumulatedPauseTime;
        $cumulativeDuration = ($now - $this->startTime) - $this->accumulatedPauseTime;

        $this->laps[] = new Lap(
            name: $name,
            duration: $lapDuration,
            cumulativeDuration: $cumulativeDuration,
        );

        $this->lastLapTime = $now;

        return $this;
    }

    /**
     * Pause the stopwatch without stopping it.
     *
     * @throws LogicException If the stopwatch has been stopped or is already paused.
     */
    public function pause(): void
    {
        $this->ensureRunning();

        if ($this->paused) {
            throw new LogicException('Stopwatch is already paused.');
        }

        $this->paused = true;
        $this->pausedAt = hrtime(true) / 1e6;
    }

    /**
     * Resume the stopwatch after being paused.
     *
     * @throws LogicException If the stopwatch has been stopped or is not paused.
     */
    public function resume(): void
    {
        $this->ensureRunning();

        if (! $this->paused) {
            throw new LogicException('Stopwatch is not paused.');
        }

        $this->accumulatedPauseTime += (hrtime(true) / 1e6) - $this->pausedAt;
        $this->paused = false;
    }

    /**
     * Get the elapsed time in seconds without stopping the stopwatch.
     */
    public function getElapsedSoFar(): float
    {
        $this->ensureRunning();

        $now = hrtime(true) / 1e6;
        $pauseAdjustment = $this->accumulatedPauseTime;

        if ($this->paused) {
            $pauseAdjustment += $now - $this->pausedAt;
        }

        return ($now - $this->startTime - $pauseAdjustment) / 1000;
    }

    /**
     * Stop the stopwatch and return the result.
     *
     * @throws LogicException If the stopwatch has already been stopped.
     */
    public function stop(): StopwatchResult
    {
        $this->ensureRunning();

        if ($this->paused) {
            $this->resume();
        }

        $endTime = hrtime(true) / 1e6;
        $endMemory = memory_get_usage();
        $endPeakMemory = memory_get_peak_usage();

        $this->running = false;

        $childResults = [];

        foreach ($this->children as $child) {
            if ($child->isRunning()) {
                $childResults[] = $child->stop();
            } else {
                $childResults[] = $child->lastResult;
            }
        }

        $this->lastResult = new StopwatchResult(
            duration: $endTime - $this->startTime - $this->accumulatedPauseTime,
            memory: $endMemory - $this->startMemory,
            peakMemory: $endPeakMemory - $this->startPeakMemory,
            laps: $this->laps,
            name: $this->name,
            children: $childResults,
        );

        return $this->lastResult;
    }

    /**
     * Get the elapsed time in milliseconds since the stopwatch was started.
     */
    public function elapsed(): float
    {
        $this->ensureRunning();

        $now = hrtime(true) / 1e6;
        $pauseAdjustment = $this->accumulatedPauseTime;

        if ($this->paused) {
            $pauseAdjustment += $now - $this->pausedAt;
        }

        return $now - $this->startTime - $pauseAdjustment;
    }

    /**
     * Check whether the stopwatch is still running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Check whether the stopwatch is currently paused.
     */
    public function isPaused(): bool
    {
        return $this->paused;
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

    /**
     * Ensure the stopwatch is not paused.
     *
     * @throws LogicException If the stopwatch is paused.
     */
    private function ensureNotPaused(): void
    {
        if ($this->paused) {
            throw new LogicException('Stopwatch is paused.');
        }
    }
}
