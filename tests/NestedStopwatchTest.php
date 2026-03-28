<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\Stopwatch;
use PhilipRehberger\Stopwatch\StopwatchResult;
use PHPUnit\Framework\TestCase;

final class NestedStopwatchTest extends TestCase
{
    public function test_child_creates_running_stopwatch(): void
    {
        $parent = Stopwatch::start('parent');
        $child = $parent->child('child-1');

        $this->assertTrue($child->isRunning());

        $child->stop();
        $parent->stop();
    }

    public function test_child_result_included_in_parent(): void
    {
        $parent = Stopwatch::start('parent');

        $child = $parent->child('db-query');
        usleep(5_000);
        $child->stop();

        usleep(5_000);
        $result = $parent->stop();

        $children = $result->children();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(StopwatchResult::class, $children[0]);
        $this->assertSame('db-query', $children[0]->name);
        $this->assertGreaterThan(0, $children[0]->duration);
    }

    public function test_multiple_children(): void
    {
        $parent = Stopwatch::start('pipeline');

        $child1 = $parent->child('step-1');
        usleep(2_000);
        $child1->stop();

        $child2 = $parent->child('step-2');
        usleep(2_000);
        $child2->stop();

        $result = $parent->stop();
        $children = $result->children();

        $this->assertCount(2, $children);
        $this->assertSame('step-1', $children[0]->name);
        $this->assertSame('step-2', $children[1]->name);
    }

    public function test_parent_stop_auto_stops_running_children(): void
    {
        $parent = Stopwatch::start('parent');
        $child = $parent->child('auto-stopped');
        usleep(5_000);

        // Stop parent without stopping child first
        $result = $parent->stop();

        $this->assertFalse($child->isRunning());
        $this->assertCount(1, $result->children());
        $this->assertGreaterThan(0, $result->children()[0]->duration);
    }

    public function test_children_appear_in_report(): void
    {
        $parent = Stopwatch::start('parent');
        $child = $parent->child('sub-task');
        usleep(2_000);
        $child->stop();
        $result = $parent->stop();

        $report = $result->report();

        $this->assertStringContainsString('Children:', $report);
        $this->assertStringContainsString('sub-task', $report);
    }

    public function test_result_with_no_children_returns_empty_array(): void
    {
        $sw = Stopwatch::start();
        $result = $sw->stop();

        $this->assertSame([], $result->children());
    }
}
