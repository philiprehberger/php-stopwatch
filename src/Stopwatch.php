<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Precise code execution timer with lap tracking and memory measurement.
 */
final class Stopwatch
{
    /**
     * Start a new stopwatch with an optional name.
     */
    public static function start(?string $name = null): RunningStopwatch
    {
        return new RunningStopwatch($name);
    }

    /**
     * Measure the execution time and memory usage of a callable.
     */
    public static function measure(callable $fn): MeasureResult
    {
        $startMemory = memory_get_usage();
        $startTime = hrtime(true);

        $fn();

        $endTime = hrtime(true);
        $endMemory = memory_get_usage();

        $durationMs = ($endTime - $startTime) / 1e6;
        $memoryDelta = $endMemory - $startMemory;

        return new MeasureResult(
            duration: $durationMs,
            memory: $memoryDelta,
        );
    }

    /**
     * Measure execution time and memory usage while preserving the callable's return value.
     *
     * @return array{result: mixed, measure: MeasureResult}
     */
    public static function measureWithResult(callable $fn): array
    {
        $startMemory = memory_get_usage();
        $startTime = hrtime(true);

        $result = $fn();

        $endTime = hrtime(true);
        $endMemory = memory_get_usage();

        $durationMs = ($endTime - $startTime) / 1e6;
        $memoryDelta = $endMemory - $startMemory;

        return [
            'result' => $result,
            'measure' => new MeasureResult(
                duration: $durationMs,
                memory: $memoryDelta,
            ),
        ];
    }
}
