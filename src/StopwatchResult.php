<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Immutable result from a completed stopwatch run, including laps and memory data.
 */
final readonly class StopwatchResult
{
    /**
     * Human-readable duration string.
     */
    public string $durationFormatted;

    /**
     * Human-readable memory delta string.
     */
    public string $memoryFormatted;

    /**
     * Create a new StopwatchResult instance.
     *
     * @param  array<Lap>  $laps
     */
    public function __construct(
        public float $duration,
        public int $memory,
        public int $peakMemory,
        public array $laps = [],
        public ?string $name = null,
    ) {
        $this->durationFormatted = Formatter::formatDuration($this->duration);
        $this->memoryFormatted = Formatter::formatBytes($this->memory);
    }

    /**
     * Generate a human-readable report of the stopwatch result including all laps.
     */
    public function report(): string
    {
        $lines = [];

        $header = $this->name !== null
            ? sprintf('Stopwatch [%s]', $this->name)
            : 'Stopwatch';

        $lines[] = $header;
        $lines[] = str_repeat('-', strlen($header));
        $lines[] = sprintf('Duration: %s', $this->durationFormatted);
        $lines[] = sprintf('Memory:   %s', $this->memoryFormatted);
        $lines[] = sprintf('Peak:     %s', Formatter::formatBytes($this->peakMemory));

        if ($this->laps !== []) {
            $lines[] = '';
            $lines[] = 'Laps:';

            foreach ($this->laps as $index => $lap) {
                $lapName = $lap->name ?? sprintf('#%d', $index + 1);
                $lines[] = sprintf(
                    '  %s — %s (cumulative: %s)',
                    $lapName,
                    Formatter::formatDuration($lap->duration),
                    Formatter::formatDuration($lap->cumulativeDuration),
                );
            }
        }

        return implode("\n", $lines);
    }
}
