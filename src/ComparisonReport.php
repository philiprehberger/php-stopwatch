<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Generates comparison reports from benchmark entries with rankings and ASCII table output.
 */
final class ComparisonReport
{
    /** @var array<string, BenchmarkEntry> */
    private array $entries;

    /**
     * Create a new ComparisonReport from named benchmark entries.
     *
     * @param  array<string, BenchmarkEntry>  $entries  Keyed by name
     */
    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    /**
     * Generate an ASCII table comparing all entries.
     *
     * Columns: Name | Mean | Median | Delta vs Best
     */
    public function toTable(): string
    {
        $rankings = $this->rankings();
        $bestMean = $rankings !== [] ? reset($rankings)->mean : 0.0;

        $nameWidth = 4;
        $meanWidth = 4;
        $medianWidth = 6;
        $deltaWidth = 10;

        $rows = [];

        foreach ($this->entries as $name => $entry) {
            $delta = $bestMean > 0.0
                ? sprintf('+%.2f%%', (($entry->mean - $bestMean) / $bestMean) * 100)
                : '+0.00%';

            if ($entry->mean === $bestMean) {
                $delta = 'baseline';
            }

            $row = [
                'name' => $name,
                'mean' => Formatter::formatDuration($entry->mean),
                'median' => Formatter::formatDuration($entry->median),
                'delta' => $delta,
            ];

            $nameWidth = max($nameWidth, strlen($name));
            $meanWidth = max($meanWidth, strlen($row['mean']));
            $medianWidth = max($medianWidth, strlen($row['median']));
            $deltaWidth = max($deltaWidth, strlen($row['delta']));

            $rows[] = $row;
        }

        $header = sprintf(
            '| %-'.$nameWidth.'s | %-'.$meanWidth.'s | %-'.$medianWidth.'s | %-'.$deltaWidth.'s |',
            'Name',
            'Mean',
            'Median',
            "\xCE\x94 vs Best",
        );

        $separator = '|'.str_repeat('-', $nameWidth + 2)
            .'|'.str_repeat('-', $meanWidth + 2)
            .'|'.str_repeat('-', $medianWidth + 2)
            .'|'.str_repeat('-', $deltaWidth + 2).'|';

        $lines = [$header, $separator];

        foreach ($rows as $row) {
            $lines[] = sprintf(
                '| %-'.$nameWidth.'s | %-'.$meanWidth.'s | %-'.$medianWidth.'s | %-'.$deltaWidth.'s |',
                $row['name'],
                $row['mean'],
                $row['median'],
                $row['delta'],
            );
        }

        return implode("\n", $lines);
    }

    /**
     * Get the fastest entry (lowest mean duration).
     */
    public function fastest(): BenchmarkEntry
    {
        $rankings = $this->rankings();

        return reset($rankings);
    }

    /**
     * Get the slowest entry (highest mean duration).
     */
    public function slowest(): BenchmarkEntry
    {
        $rankings = $this->rankings();

        return end($rankings);
    }

    /**
     * Get entries sorted by mean duration (ascending).
     *
     * @return array<string, BenchmarkEntry>
     */
    public function rankings(): array
    {
        $sorted = $this->entries;
        uasort($sorted, fn (BenchmarkEntry $a, BenchmarkEntry $b): int => $a->mean <=> $b->mean);

        return $sorted;
    }

    /**
     * Get all entries as an associative array.
     *
     * @return array<string, BenchmarkEntry>
     */
    public function toArray(): array
    {
        return $this->entries;
    }
}
