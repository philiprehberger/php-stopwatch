<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\MeasureResult;
use PhilipRehberger\Stopwatch\Stopwatch;
use PhilipRehberger\Stopwatch\StopwatchResult;
use PhilipRehberger\Stopwatch\ThresholdMonitor;
use PHPUnit\Framework\TestCase;

final class ThresholdTest extends TestCase
{
    public function test_callback_fires_when_threshold_exceeded(): void
    {
        $fired = false;

        Stopwatch::measureWithThreshold(
            callback: function () {
                usleep(20_000); // ~20ms
            },
            thresholdMs: 5.0,
            onExceeded: function (MeasureResult $result) use (&$fired) {
                $fired = true;
                $this->assertGreaterThan(5.0, $result->duration);
            },
        );

        $this->assertTrue($fired);
    }

    public function test_callback_does_not_fire_when_under_threshold(): void
    {
        $fired = false;

        Stopwatch::measureWithThreshold(
            callback: function () {
                // Near-instant operation
            },
            thresholdMs: 1000.0,
            onExceeded: function () use (&$fired) {
                $fired = true;
            },
        );

        $this->assertFalse($fired);
    }

    public function test_measure_with_threshold_returns_measure_result(): void
    {
        $result = Stopwatch::measureWithThreshold(
            callback: function () {
                usleep(1_000);
            },
            thresholdMs: 5000.0,
            onExceeded: function () {},
        );

        $this->assertInstanceOf(MeasureResult::class, $result);
        $this->assertGreaterThan(0, $result->duration);
    }

    public function test_multiple_thresholds(): void
    {
        $monitor = new ThresholdMonitor;
        $firedAt = [];

        $monitor->addThreshold(5.0, function () use (&$firedAt) {
            $firedAt[] = 5.0;
        });

        $monitor->addThreshold(10.0, function () use (&$firedAt) {
            $firedAt[] = 10.0;
        });

        $monitor->addThreshold(100.0, function () use (&$firedAt) {
            $firedAt[] = 100.0;
        });

        // Duration of 50ms should fire 5ms and 10ms thresholds, but not 100ms
        $result = new MeasureResult(duration: 50.0, memory: 0);
        $monitor->check($result);

        $this->assertSame([5.0, 10.0], $firedAt);
    }

    public function test_threshold_monitor_with_stopwatch_result(): void
    {
        $monitor = new ThresholdMonitor;
        $fired = false;

        $monitor->addThreshold(1.0, function (StopwatchResult $result) use (&$fired) {
            $fired = true;
            $this->assertGreaterThan(1.0, $result->duration);
        });

        $result = new StopwatchResult(
            duration: 50.0,
            memory: 0,
            peakMemory: 0,
        );

        $monitor->check($result);

        $this->assertTrue($fired);
    }

    public function test_running_stopwatch_on_threshold_fires_on_stop(): void
    {
        $fired = false;

        $sw = Stopwatch::start();

        $sw->onThreshold(0.001, function (StopwatchResult $result) use (&$fired) {
            $fired = true;
            $this->assertGreaterThan(0.001, $result->duration);
        });

        usleep(5_000); // ~5ms
        $sw->stop();

        $this->assertTrue($fired);
    }

    public function test_running_stopwatch_on_threshold_does_not_fire_when_under(): void
    {
        $fired = false;

        $sw = Stopwatch::start();

        $sw->onThreshold(999_999.0, function () use (&$fired) {
            $fired = true;
        });

        $sw->stop();

        $this->assertFalse($fired);
    }

    public function test_running_stopwatch_multiple_thresholds(): void
    {
        $firedAt = [];

        $sw = Stopwatch::start();

        $sw->onThreshold(0.001, function () use (&$firedAt) {
            $firedAt[] = 0.001;
        });

        $sw->onThreshold(0.002, function () use (&$firedAt) {
            $firedAt[] = 0.002;
        });

        $sw->onThreshold(999_999.0, function () use (&$firedAt) {
            $firedAt[] = 999_999.0;
        });

        usleep(5_000);
        $sw->stop();

        $this->assertSame([0.001, 0.002], $firedAt);
    }

    public function test_threshold_monitor_fluent_interface(): void
    {
        $monitor = new ThresholdMonitor;

        $result = $monitor
            ->addThreshold(10.0, function () {})
            ->addThreshold(20.0, function () {});

        $this->assertInstanceOf(ThresholdMonitor::class, $result);
    }

    public function test_running_stopwatch_on_threshold_fluent_interface(): void
    {
        $sw = Stopwatch::start();

        $result = $sw
            ->onThreshold(10.0, function () {})
            ->onThreshold(20.0, function () {});

        $this->assertSame($sw, $result);

        $sw->stop();
    }
}
