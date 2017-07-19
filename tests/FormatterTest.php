<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\Formatter;
use PHPUnit\Framework\TestCase;

final class FormatterTest extends TestCase
{
    // ---------------------------------------------------------------
    // formatDuration — zero
    // ---------------------------------------------------------------

    public function test_format_duration_zero(): void
    {
        $this->assertSame('0.00ms', Formatter::formatDuration(0.0));
    }

    // ---------------------------------------------------------------
    // formatDuration — sub-millisecond
    // ---------------------------------------------------------------

    public function test_format_duration_sub_millisecond(): void
    {
        $this->assertSame('0.01ms', Formatter::formatDuration(0.01));
        $this->assertSame('0.10ms', Formatter::formatDuration(0.1));
    }

    // ---------------------------------------------------------------
    // formatDuration — milliseconds range
    // ---------------------------------------------------------------

    public function test_format_duration_milliseconds(): void
    {
        $this->assertSame('1.00ms', Formatter::formatDuration(1.0));
        $this->assertSame('500.00ms', Formatter::formatDuration(500.0));
        $this->assertSame('999.99ms', Formatter::formatDuration(999.99));
    }

    // ---------------------------------------------------------------
    // formatDuration — boundary between ms and s
    // ---------------------------------------------------------------

    public function test_format_duration_ms_to_s_boundary(): void
    {
        $this->assertSame('999.99ms', Formatter::formatDuration(999.99));
        $this->assertSame('1.00s', Formatter::formatDuration(1000.0));
        $this->assertSame('1.00s', Formatter::formatDuration(1000.01));
    }

    // ---------------------------------------------------------------
    // formatDuration — seconds range
    // ---------------------------------------------------------------

    public function test_format_duration_seconds(): void
    {
        $this->assertSame('1.50s', Formatter::formatDuration(1500.0));
        $this->assertSame('30.00s', Formatter::formatDuration(30_000.0));
        $this->assertSame('59.98s', Formatter::formatDuration(59_983.33));
    }

    // ---------------------------------------------------------------
    // formatDuration — boundary between s and m
    // ---------------------------------------------------------------

    public function test_format_duration_s_to_m_boundary(): void
    {
        $this->assertSame('59.99s', Formatter::formatDuration(59_990.0));
        $this->assertSame('1.00m', Formatter::formatDuration(60_000.0));
        $this->assertSame('1.00m', Formatter::formatDuration(60_001.0));
    }

    // ---------------------------------------------------------------
    // formatDuration — minutes range
    // ---------------------------------------------------------------

    public function test_format_duration_minutes(): void
    {
        $this->assertSame('5.00m', Formatter::formatDuration(300_000.0));
        $this->assertSame('60.00m', Formatter::formatDuration(3_600_000.0));
    }

    // ---------------------------------------------------------------
    // formatDuration — large values
    // ---------------------------------------------------------------

    public function test_format_duration_very_large(): void
    {
        // 24 hours in milliseconds = 86_400_000
        $this->assertSame('1440.00m', Formatter::formatDuration(86_400_000.0));
    }

    // ---------------------------------------------------------------
    // formatDuration — negative values
    // ---------------------------------------------------------------

    public function test_format_duration_negative_milliseconds(): void
    {
        $this->assertSame('-5.00ms', Formatter::formatDuration(-5.0));
    }

    public function test_format_duration_negative_seconds(): void
    {
        $this->assertSame('-2.00s', Formatter::formatDuration(-2000.0));
    }

    public function test_format_duration_negative_minutes(): void
    {
        $this->assertSame('-1.50m', Formatter::formatDuration(-90_000.0));
    }

    // ---------------------------------------------------------------
    // formatBytes — zero
    // ---------------------------------------------------------------

    public function test_format_bytes_zero(): void
    {
        $this->assertSame('0B', Formatter::formatBytes(0));
    }

    // ---------------------------------------------------------------
    // formatBytes — bytes range
    // ---------------------------------------------------------------

    public function test_format_bytes_small(): void
    {
        $this->assertSame('1B', Formatter::formatBytes(1));
        $this->assertSame('512B', Formatter::formatBytes(512));
        $this->assertSame('1023B', Formatter::formatBytes(1023));
    }

    // ---------------------------------------------------------------
    // formatBytes — boundary between B and KB
    // ---------------------------------------------------------------

    public function test_format_bytes_b_to_kb_boundary(): void
    {
        $this->assertSame('1023B', Formatter::formatBytes(1023));
        $this->assertSame('1.00KB', Formatter::formatBytes(1024));
        $this->assertSame('1.00KB', Formatter::formatBytes(1025));
    }

    // ---------------------------------------------------------------
    // formatBytes — kilobytes range
    // ---------------------------------------------------------------

    public function test_format_bytes_kilobytes(): void
    {
        $this->assertSame('1.50KB', Formatter::formatBytes(1536));
        $this->assertSame('500.00KB', Formatter::formatBytes(512_000));
    }

    // ---------------------------------------------------------------
    // formatBytes — boundary between KB and MB
    // ---------------------------------------------------------------

    public function test_format_bytes_kb_to_mb_boundary(): void
    {
        $this->assertSame('1024.00KB', Formatter::formatBytes(1_048_575));
        $this->assertSame('1.00MB', Formatter::formatBytes(1_048_576));
    }

    // ---------------------------------------------------------------
    // formatBytes — megabytes range
    // ---------------------------------------------------------------

    public function test_format_bytes_megabytes(): void
    {
        $this->assertSame('5.00MB', Formatter::formatBytes(5_242_880));
        $this->assertSame('100.00MB', Formatter::formatBytes(104_857_600));
    }

    // ---------------------------------------------------------------
    // formatBytes — boundary between MB and GB
    // ---------------------------------------------------------------

    public function test_format_bytes_mb_to_gb_boundary(): void
    {
        $this->assertSame('1024.00MB', Formatter::formatBytes(1_073_741_823));
        $this->assertSame('1.00GB', Formatter::formatBytes(1_073_741_824));
    }

    // ---------------------------------------------------------------
    // formatBytes — gigabytes range
    // ---------------------------------------------------------------

    public function test_format_bytes_gigabytes(): void
    {
        $this->assertSame('2.00GB', Formatter::formatBytes(2_147_483_648));
    }

    // ---------------------------------------------------------------
    // formatBytes — large values
    // ---------------------------------------------------------------

    public function test_format_bytes_very_large(): void
    {
        // 10 GB
        $this->assertSame('10.00GB', Formatter::formatBytes(10_737_418_240));
    }

    // ---------------------------------------------------------------
    // formatBytes — negative values
    // ---------------------------------------------------------------

    public function test_format_bytes_negative_bytes(): void
    {
        $this->assertSame('-100B', Formatter::formatBytes(-100));
    }

    public function test_format_bytes_negative_kilobytes(): void
    {
        $this->assertSame('-1.00KB', Formatter::formatBytes(-1024));
    }

    public function test_format_bytes_negative_megabytes(): void
    {
        $this->assertSame('-1.00MB', Formatter::formatBytes(-1_048_576));
    }

    public function test_format_bytes_negative_gigabytes(): void
    {
        $this->assertSame('-1.00GB', Formatter::formatBytes(-1_073_741_824));
    }
}
