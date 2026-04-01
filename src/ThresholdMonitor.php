<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Monitors timing results against configurable thresholds and fires callbacks when exceeded.
 */
final class ThresholdMonitor
{
    /** @var array<array{ms: float, callback: callable}> */
    private array $thresholds = [];

    /**
     * Register a threshold that fires a callback when the duration exceeds the given milliseconds.
     *
     * @param  float  $ms  Threshold in milliseconds
     * @param  callable  $callback  Receives the result object when threshold is exceeded
     */
    public function addThreshold(float $ms, callable $callback): self
    {
        $this->thresholds[] = ['ms' => $ms, 'callback' => $callback];

        return $this;
    }

    /**
     * Check a result against all registered thresholds and fire matching callbacks.
     */
    public function check(StopwatchResult|MeasureResult $result): void
    {
        foreach ($this->thresholds as $threshold) {
            if ($result->duration > $threshold['ms']) {
                ($threshold['callback'])($result);
            }
        }
    }
}
