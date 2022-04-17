<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Statistical analysis of stopwatch durations (all values in milliseconds).
 */
final readonly class StopwatchStats
{
    public float $mean;

    public float $median;

    public float $min;

    public float $max;

    public float $p95;

    public float $p99;

    public float $standardDeviation;

    /**
     * Create a new StopwatchStats instance from an array of durations in milliseconds.
     *
     * @param  array<float>  $durations
     */
    public function __construct(array $durations)
    {
        $sorted = $durations;
        sort($sorted);

        $count = count($sorted);

        $this->min = $sorted[0];
        $this->max = $sorted[$count - 1];
        $this->mean = array_sum($sorted) / $count;
        $this->median = self::percentile($sorted, 50.0);
        $this->p95 = self::percentile($sorted, 95.0);
        $this->p99 = self::percentile($sorted, 99.0);

        $variance = 0.0;

        foreach ($sorted as $value) {
            $variance += ($value - $this->mean) ** 2;
        }

        $this->standardDeviation = sqrt($variance / $count);
    }

    /**
     * Get the mean duration in milliseconds.
     */
    public function mean(): float
    {
        return $this->mean;
    }

    /**
     * Get the median duration in milliseconds.
     */
    public function median(): float
    {
        return $this->median;
    }

    /**
     * Get the minimum duration in milliseconds.
     */
    public function min(): float
    {
        return $this->min;
    }

    /**
     * Get the maximum duration in milliseconds.
     */
    public function max(): float
    {
        return $this->max;
    }

    /**
     * Get the 95th percentile duration in milliseconds.
     */
    public function p95(): float
    {
        return $this->p95;
    }

    /**
     * Get the 99th percentile duration in milliseconds.
     */
    public function p99(): float
    {
        return $this->p99;
    }

    /**
     * Get the standard deviation in milliseconds.
     */
    public function standardDeviation(): float
    {
        return $this->standardDeviation;
    }

    /**
     * Calculate a percentile value from a sorted array using linear interpolation.
     *
     * @param  array<float>  $sorted
     */
    private static function percentile(array $sorted, float $percentile): float
    {
        $count = count($sorted);

        if ($count === 1) {
            return $sorted[0];
        }

        $rank = ($percentile / 100) * ($count - 1);
        $lower = (int) floor($rank);
        $upper = (int) ceil($rank);
        $fraction = $rank - $lower;

        if ($lower === $upper) {
            return $sorted[$lower];
        }

        return $sorted[$lower] + $fraction * ($sorted[$upper] - $sorted[$lower]);
    }
}
