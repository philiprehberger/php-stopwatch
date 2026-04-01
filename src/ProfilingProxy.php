<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Wraps an object and profiles all public method calls with timing data.
 *
 * Uses __call() to intercept method calls on the wrapped object, timing each
 * invocation and storing per-method statistics (call count, total time, min, max).
 */
final class ProfilingProxy
{
    /** @var array<string, array{count: int, total: float, min: float, max: float}> */
    private array $profile = [];

    /**
     * Create a new ProfilingProxy wrapping the given target object.
     *
     * @internal Use Stopwatch::profile() to create instances.
     */
    public function __construct(
        private readonly object $target,
    ) {}

    /**
     * Intercept method calls, forward them to the target, and record timing data.
     *
     * @param  array<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $start = hrtime(true);
        $result = $this->target->{$name}(...$arguments);
        $elapsed = (hrtime(true) - $start) / 1e6;

        if (! isset($this->profile[$name])) {
            $this->profile[$name] = [
                'count' => 0,
                'total' => 0.0,
                'min' => PHP_FLOAT_MAX,
                'max' => 0.0,
            ];
        }

        $this->profile[$name]['count']++;
        $this->profile[$name]['total'] += $elapsed;
        $this->profile[$name]['min'] = min($this->profile[$name]['min'], $elapsed);
        $this->profile[$name]['max'] = max($this->profile[$name]['max'], $elapsed);

        return $result;
    }

    /**
     * Get profiling data for all intercepted methods.
     *
     * @return array<string, StopwatchStats>
     */
    public function getProfile(): array
    {
        $stats = [];

        foreach ($this->profile as $method => $data) {
            $mean = $data['total'] / $data['count'];
            $durations = array_fill(0, $data['count'], $mean);

            if ($data['count'] === 1) {
                $durations = [$data['total']];
            } else {
                $durations = [];
                $durations[] = $data['min'];
                $durations[] = $data['max'];

                $remaining = $data['count'] - 2;

                if ($remaining > 0) {
                    $remainingTotal = $data['total'] - $data['min'] - $data['max'];
                    $avgRemaining = $remainingTotal / $remaining;

                    for ($i = 0; $i < $remaining; $i++) {
                        $durations[] = $avgRemaining;
                    }
                }
            }

            $stats[$method] = new StopwatchStats($durations);
        }

        return $stats;
    }

    /**
     * Get raw profiling data for all intercepted methods.
     *
     * @return array<string, array{count: int, total: float, min: float, max: float}>
     */
    public function getRawProfile(): array
    {
        return $this->profile;
    }

    /**
     * Get the wrapped target object.
     */
    public function getTarget(): object
    {
        return $this->target;
    }
}
