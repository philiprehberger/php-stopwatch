<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use LogicException;
use PhilipRehberger\Stopwatch\Formatter;
use PhilipRehberger\Stopwatch\MeasureResult;
use PhilipRehberger\Stopwatch\Stopwatch;
use PhilipRehberger\Stopwatch\StopwatchResult;
use PHPUnit\Framework\TestCase;

final class StopwatchTest extends TestCase
{
    public function test_measure_returns_positive_duration(): void
    {
        $result = Stopwatch::measure(function () {
            usleep(10_000); // 10ms
        });

        $this->assertInstanceOf(MeasureResult::class, $result);
        $this->assertGreaterThan(0, $result->duration);
    }

    public function test_measure_tracks_memory_delta(): void
    {
        $result = Stopwatch::measure(function () {
            // Allocate ~100KB of memory
            $data = str_repeat('x', 100_000);
            // Keep reference alive to prevent GC
            strlen($data);
        });

        $this->assertIsInt($result->memory);
    }

    public function test_start_stop_produces_valid_result(): void
    {
        $sw = Stopwatch::start();
        usleep(5_000); // 5ms
        $result = $sw->stop();

        $this->assertInstanceOf(StopwatchResult::class, $result);
        $this->assertGreaterThan(0, $result->duration);
        $this->assertIsInt($result->memory);
        $this->assertIsInt($result->peakMemory);
        $this->assertIsArray($result->laps);
    }

    public function test_lap_recording_preserves_order_and_names(): void
    {
        $sw = Stopwatch::start();
        usleep(1_000);
        $sw->lap('first');
        usleep(1_000);
        $sw->lap('second');
        usleep(1_000);
        $sw->lap('third');
        $result = $sw->stop();

        $this->assertCount(3, $result->laps);
        $this->assertSame('first', $result->laps[0]->name);
        $this->assertSame('second', $result->laps[1]->name);
        $this->assertSame('third', $result->laps[2]->name);

        // Cumulative durations should be increasing
        $this->assertLessThan(
            $result->laps[1]->cumulativeDuration,
            $result->laps[0]->cumulativeDuration,
        );
        $this->assertLessThan(
            $result->laps[2]->cumulativeDuration,
            $result->laps[1]->cumulativeDuration,
        );
    }

    public function test_duration_formatted_switches_units(): void
    {
        $this->assertSame('0.50ms', Formatter::formatDuration(0.5));
        $this->assertSame('999.00ms', Formatter::formatDuration(999.0));
        $this->assertSame('1.00s', Formatter::formatDuration(1000.0));
        $this->assertSame('59.99s', Formatter::formatDuration(59_990.0));
        $this->assertSame('1.00m', Formatter::formatDuration(60_000.0));
        $this->assertSame('2.50m', Formatter::formatDuration(150_000.0));
    }

    public function test_memory_formatted_switches_units(): void
    {
        $this->assertSame('512B', Formatter::formatBytes(512));
        $this->assertSame('1.00KB', Formatter::formatBytes(1024));
        $this->assertSame('1.50KB', Formatter::formatBytes(1536));
        $this->assertSame('1.00MB', Formatter::formatBytes(1_048_576));
        $this->assertSame('1.00GB', Formatter::formatBytes(1_073_741_824));
    }

    public function test_elapsed_on_running_stopwatch_returns_positive_value(): void
    {
        $sw = Stopwatch::start();
        usleep(1_000);
        $elapsed = $sw->elapsed();

        $this->assertGreaterThan(0, $elapsed);
        $this->assertTrue($sw->isRunning());

        $sw->stop();
    }

    public function test_report_includes_all_laps(): void
    {
        $sw = Stopwatch::start('benchmark');
        usleep(1_000);
        $sw->lap('setup');
        usleep(1_000);
        $sw->lap('execute');
        $result = $sw->stop();

        $report = $result->report();

        $this->assertStringContainsString('Stopwatch [benchmark]', $report);
        $this->assertStringContainsString('Duration:', $report);
        $this->assertStringContainsString('Memory:', $report);
        $this->assertStringContainsString('Peak:', $report);
        $this->assertStringContainsString('Laps:', $report);
        $this->assertStringContainsString('setup', $report);
        $this->assertStringContainsString('execute', $report);
    }

    public function test_measure_with_result_returns_closure_value(): void
    {
        $outcome = Stopwatch::measureWithResult(function () {
            return 42;
        });

        $this->assertArrayHasKey('result', $outcome);
        $this->assertArrayHasKey('measure', $outcome);
        $this->assertSame(42, $outcome['result']);
        $this->assertInstanceOf(MeasureResult::class, $outcome['measure']);
        $this->assertGreaterThan(0, $outcome['measure']->duration);
    }

    public function test_named_stopwatch(): void
    {
        $sw = Stopwatch::start('my-timer');
        $result = $sw->stop();

        $this->assertSame('my-timer', $result->name);
        $this->assertStringContainsString('my-timer', $result->report());
    }

    public function test_double_stop_throws_logic_exception(): void
    {
        $sw = Stopwatch::start();
        $sw->stop();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stopwatch has already been stopped.');
        $sw->stop();
    }

    public function test_zero_duration_edge_case(): void
    {
        $result = Stopwatch::measure(function () {
            // No-op — should complete in near-zero time
        });

        $this->assertGreaterThanOrEqual(0.0, $result->duration);
        $this->assertNotEmpty($result->durationFormatted);
        $this->assertNotEmpty($result->memoryFormatted);
    }

    public function test_pause_and_resume_excludes_paused_time(): void
    {
        $sw = Stopwatch::start();
        usleep(10_000); // 10ms active

        $sw->pause();
        usleep(50_000); // 50ms paused — should not be counted
        $sw->resume();

        usleep(10_000); // 10ms active
        $result = $sw->stop();

        // Total active time ~20ms, paused ~50ms was excluded
        // Duration should be well under 50ms (the paused portion)
        $this->assertLessThan(45.0, $result->duration);
        $this->assertGreaterThan(5.0, $result->duration);
    }

    public function test_get_elapsed_so_far_returns_seconds_while_running(): void
    {
        $sw = Stopwatch::start();
        usleep(20_000); // 20ms

        $elapsed = $sw->getElapsedSoFar();

        $this->assertGreaterThan(0.0, $elapsed);
        // Should be in seconds (20ms = ~0.02s), not milliseconds
        $this->assertLessThan(1.0, $elapsed);
        $this->assertTrue($sw->isRunning());

        $sw->stop();
    }

    public function test_get_elapsed_so_far_excludes_paused_time(): void
    {
        $sw = Stopwatch::start();
        usleep(10_000); // 10ms active

        $sw->pause();
        usleep(50_000); // 50ms paused
        $elapsed = $sw->getElapsedSoFar();
        $sw->resume();

        // Elapsed should reflect ~10ms active, not the 50ms pause
        $this->assertLessThan(0.045, $elapsed);

        $sw->stop();
    }

    public function test_resume_without_pause_throws_logic_exception(): void
    {
        $sw = Stopwatch::start();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stopwatch is not paused.');
        $sw->resume();
    }

    public function test_pause_while_already_paused_throws_logic_exception(): void
    {
        $sw = Stopwatch::start();
        $sw->pause();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stopwatch is already paused.');
        $sw->pause();
    }

    public function test_is_paused_reflects_pause_state(): void
    {
        $sw = Stopwatch::start();
        $this->assertFalse($sw->isPaused());

        $sw->pause();
        $this->assertTrue($sw->isPaused());

        $sw->resume();
        $this->assertFalse($sw->isPaused());

        $sw->stop();
    }

    public function test_stop_while_paused_auto_resumes(): void
    {
        $sw = Stopwatch::start();
        usleep(10_000);
        $sw->pause();
        usleep(50_000); // paused time — should be excluded
        $result = $sw->stop();

        $this->assertLessThan(45.0, $result->duration);
        $this->assertFalse($sw->isRunning());
    }
}
