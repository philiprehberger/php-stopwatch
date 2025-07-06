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

    /**
     * Measure a callable and fire a callback if the duration exceeds the given threshold.
     *
     * @param  callable  $callback  The code to measure
     * @param  float  $thresholdMs  Threshold in milliseconds
     * @param  callable  $onExceeded  Callback receiving the MeasureResult when threshold is exceeded
     */
    public static function measureWithThreshold(callable $callback, float $thresholdMs, callable $onExceeded): MeasureResult
    {
        $result = self::measure($callback);

        $monitor = new ThresholdMonitor;
        $monitor->addThreshold($thresholdMs, $onExceeded);
        $monitor->check($result);

        return $result;
    }

    /**
     * Compare named callables by running each for a given number of iterations.
     *
     * @param  array<string, callable>  $benchmarks  Named callables to compare
     * @param  int  $iterations  Number of iterations per callable
     */
    public static function compare(array $benchmarks, int $iterations = 100): ComparisonReport
    {
        $entries = [];

        foreach ($benchmarks as $name => $callable) {
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

            $entries[$name] = new BenchmarkEntry(
                mean: $mean,
                median: $median,
                min: $min,
                max: $max,
                iterations: $iterations,
            );
        }

        return new ComparisonReport($entries);
    }

    /**
     * Create a profiling proxy that times all method calls on the target object.
     */
    public static function profile(object $target): ProfilingProxy
    {
        return new ProfilingProxy($target);
    }

    /**
     * Get profiling data from a ProfilingProxy instance.
     *
     * @return array<string, StopwatchStats>
     */
    public static function getProfile(ProfilingProxy $proxy): array
    {
        return $proxy->getProfile();
    }
}
