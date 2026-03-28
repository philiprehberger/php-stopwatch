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
     * Benchmark multiple callables by running each for a given number of iterations.
     *
     * @param  array<callable>  $callables
     */
    public static function benchmark(array $callables, int $iterations = 100): BenchmarkResult
    {
        $entries = [];

        foreach ($callables as $index => $callable) {
            $durations = [];

            for ($i = 0; $i < $iterations; $i++) {
                $start = hrtime(true);
                $callable();
                $end = hrtime(true);
                $durations[] = ($end - $start) / 1e6;
            }

            sort($durations);
            $count = count($durations);
            $sum = array_sum($durations);

            $mean = $sum / $count;
            $mid = intdiv($count, 2);
            $median = $count % 2 === 0
                ? ($durations[$mid - 1] + $durations[$mid]) / 2
                : $durations[$mid];
            $min = $durations[0];
            $max = $durations[$count - 1];

            $entries[$index] = new BenchmarkEntry(
                mean: $mean,
                median: $median,
                min: $min,
                max: $max,
                iterations: $iterations,
            );
        }

        return new BenchmarkResult($entries);
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
