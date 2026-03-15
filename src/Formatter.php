<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch;

/**
 * Static helpers for formatting durations and byte sizes to human-readable strings.
 */
final class Formatter
{
    /**
     * Format a duration in milliseconds to a human-readable string.
     *
     * Returns values like "0.12ms", "345.67ms", "1.23s", "2.50m".
     */
    public static function formatDuration(float $milliseconds): string
    {
        $abs = abs($milliseconds);

        if ($abs < 1000) {
            return sprintf('%.2fms', $milliseconds);
        }

        $seconds = $milliseconds / 1000;

        if ($abs < 60_000) {
            return sprintf('%.2fs', $seconds);
        }

        $minutes = $seconds / 60;

        return sprintf('%.2fm', $minutes);
    }

    /**
     * Format a byte count to a human-readable string.
     *
     * Returns values like "512B", "1.50KB", "3.25MB", "1.10GB".
     */
    public static function formatBytes(int $bytes): string
    {
        $abs = abs($bytes);
        $sign = $bytes < 0 ? '-' : '';

        if ($abs < 1024) {
            return sprintf('%s%dB', $sign, $abs);
        }

        $kb = $abs / 1024;

        if ($kb < 1024) {
            return sprintf('%s%.2fKB', $sign, $kb);
        }

        $mb = $kb / 1024;

        if ($mb < 1024) {
            return sprintf('%s%.2fMB', $sign, $mb);
        }

        $gb = $mb / 1024;

        return sprintf('%s%.2fGB', $sign, $gb);
    }
}
