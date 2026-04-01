<?php

declare(strict_types=1);

namespace PhilipRehberger\Stopwatch\Tests;

use PhilipRehberger\Stopwatch\ProfilingProxy;
use PhilipRehberger\Stopwatch\Stopwatch;
use PhilipRehberger\Stopwatch\StopwatchStats;
use PHPUnit\Framework\TestCase;

final class ProfilingProxyTest extends TestCase
{
    public function test_method_calls_are_forwarded_correctly(): void
    {
        $target = new class
        {
            public function add(int $a, int $b): int
            {
                return $a + $b;
            }

            public function greet(string $name): string
            {
                return "Hello, {$name}!";
            }
        };

        $proxy = Stopwatch::profile($target);

        $this->assertSame(5, $proxy->add(2, 3));
        $this->assertSame('Hello, World!', $proxy->greet('World'));
    }

    public function test_get_profile_has_timing_data(): void
    {
        $target = new class
        {
            public function work(): void
            {
                usleep(1_000); // ~1ms
            }
        };

        $proxy = Stopwatch::profile($target);
        $proxy->work();
        $proxy->work();
        $proxy->work();

        $profile = $proxy->getProfile();

        $this->assertArrayHasKey('work', $profile);
        $this->assertInstanceOf(StopwatchStats::class, $profile['work']);
        $this->assertGreaterThan(0, $profile['work']->mean());
    }

    public function test_tracks_call_counts(): void
    {
        $target = new class
        {
            public function doSomething(): void {}

            public function doOther(): void {}
        };

        $proxy = Stopwatch::profile($target);
        $proxy->doSomething();
        $proxy->doSomething();
        $proxy->doSomething();
        $proxy->doOther();

        $rawProfile = $proxy->getRawProfile();

        $this->assertSame(3, $rawProfile['doSomething']['count']);
        $this->assertSame(1, $rawProfile['doOther']['count']);
    }

    public function test_tracks_min_and_max(): void
    {
        $target = new class
        {
            public function variable(int $sleepUs): void
            {
                usleep($sleepUs);
            }
        };

        $proxy = Stopwatch::profile($target);
        $proxy->variable(1_000);  // ~1ms
        $proxy->variable(5_000);  // ~5ms

        $rawProfile = $proxy->getRawProfile();

        $this->assertLessThan($rawProfile['variable']['max'], $rawProfile['variable']['min']);
        $this->assertGreaterThan(0, $rawProfile['variable']['total']);
    }

    public function test_get_target_returns_wrapped_object(): void
    {
        $target = new class
        {
            public string $name = 'test';
        };

        $proxy = Stopwatch::profile($target);

        $this->assertSame($target, $proxy->getTarget());
        $this->assertSame('test', $proxy->getTarget()->name);
    }

    public function test_static_get_profile_returns_same_as_instance(): void
    {
        $target = new class
        {
            public function ping(): string
            {
                return 'pong';
            }
        };

        $proxy = Stopwatch::profile($target);
        $proxy->ping();

        $instanceProfile = $proxy->getProfile();
        $staticProfile = Stopwatch::getProfile($proxy);

        $this->assertSame(array_keys($instanceProfile), array_keys($staticProfile));
        $this->assertArrayHasKey('ping', $staticProfile);
    }

    public function test_profile_returns_profiling_proxy(): void
    {
        $target = new class {};

        $proxy = Stopwatch::profile($target);

        $this->assertInstanceOf(ProfilingProxy::class, $proxy);
    }

    public function test_profile_with_return_values(): void
    {
        $target = new class
        {
            public function multiply(float $a, float $b): float
            {
                return $a * $b;
            }

            public function getArray(): array
            {
                return [1, 2, 3];
            }
        };

        $proxy = Stopwatch::profile($target);

        $this->assertSame(6.0, $proxy->multiply(2.0, 3.0));
        $this->assertSame([1, 2, 3], $proxy->getArray());
    }

    public function test_empty_profile_before_any_calls(): void
    {
        $target = new class
        {
            public function unused(): void {}
        };

        $proxy = Stopwatch::profile($target);

        $this->assertSame([], $proxy->getProfile());
        $this->assertSame([], $proxy->getRawProfile());
    }
}
