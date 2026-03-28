# PHP Stopwatch

[![CI](https://github.com/philiprehberger/php-stopwatch/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-stopwatch/actions/workflows/tests.yml)
[![Packagist Version](https://img.shields.io/packagist/v/philiprehberger/php-stopwatch)](https://packagist.org/packages/philiprehberger/php-stopwatch)
[![GitHub Release](https://img.shields.io/github/v/release/philiprehberger/php-stopwatch)](https://github.com/philiprehberger/php-stopwatch/releases)
[![Last Updated](https://img.shields.io/github/last-commit/philiprehberger/php-stopwatch)](https://github.com/philiprehberger/php-stopwatch/commits/main)
[![License](https://img.shields.io/github/license/philiprehberger/php-stopwatch)](LICENSE)
[![Bug Reports](https://img.shields.io/github/issues/philiprehberger/php-stopwatch/bug)](https://github.com/philiprehberger/php-stopwatch/issues?q=is%3Aissue+is%3Aopen+label%3Abug)
[![Feature Requests](https://img.shields.io/github/issues/philiprehberger/php-stopwatch/enhancement)](https://github.com/philiprehberger/php-stopwatch/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)
[![Sponsor](https://img.shields.io/badge/sponsor-GitHub%20Sponsors-ec6cb9)](https://github.com/sponsors/philiprehberger)

Precise code execution timer with lap tracking and memory measurement.

## Requirements

- PHP 8.2+

## Installation

```bash
composer require philiprehberger/php-stopwatch
```

## Usage

### Quick measurement

```php
use PhilipRehberger\Stopwatch\Stopwatch;

$result = Stopwatch::measure(function () {
    // Code to measure
    file_get_contents('https://example.com');
});

echo $result->durationFormatted; // "123.45ms"
echo $result->memoryFormatted;   // "1.50KB"
```

### Measurement with return value

```php
$outcome = Stopwatch::measureWithResult(function () {
    return User::query()->where('active', true)->get();
});

$users = $outcome['result'];
echo $outcome['measure']->durationFormatted; // "45.12ms"
```

### Manual start/stop with laps

```php
$sw = Stopwatch::start('data-pipeline');

// Phase 1
$data = loadData();
$sw->lap('load');

// Phase 2
$transformed = transform($data);
$sw->lap('transform');

// Phase 3
save($transformed);
$sw->lap('save');

$result = $sw->stop();

echo $result->report();
// Stopwatch [data-pipeline]
// -------------------------
// Duration: 1.23s
// Memory:   3.25MB
// Peak:     5.10MB
//
// Laps:
//   load — 200.00ms (cumulative: 200.00ms)
//   transform — 800.00ms (cumulative: 1000.00ms)
//   save — 230.00ms (cumulative: 1230.00ms)
```

### Check elapsed time while running

```php
$sw = Stopwatch::start();

doSomeWork();

if ($sw->elapsed() > 5000) {
    // Already over 5 seconds, skip remaining work
}

$result = $sw->stop();
```

### Pause and resume

```php
$sw = Stopwatch::start();

$data = fetchFromApi();
$sw->pause();  // Pause while waiting for user input

$input = readline('Continue? ');

$sw->resume(); // Resume timing
processData($data, $input);

echo $sw->getElapsedSoFar() . 's elapsed so far';

$result = $sw->stop();
echo $result->durationFormatted; // Excludes time spent paused
```

### Nested stopwatches

```php
$sw = Stopwatch::start('parent');

$child = $sw->child('database');
// ... database queries ...
$childResult = $child->stop();

$result = $sw->stop();

foreach ($result->children() as $child) {
    echo $child->name . ': ' . $child->durationFormatted;
}
```

### Benchmark comparisons

```php
$result = Stopwatch::benchmark([
    fn () => array_map(fn ($x) => $x * 2, range(1, 1000)),
    fn () => array_walk($arr = range(1, 1000), fn (&$x) => $x *= 2),
], iterations: 100);

echo $result->report();
// Benchmark Results
// ------------------
// #0 — mean: 0.12ms | median: 0.11ms | min: 0.09ms | max: 0.25ms (100 iterations)
// #1 — mean: 0.15ms | median: 0.14ms | min: 0.11ms | max: 0.30ms (100 iterations)
```

### Statistical analysis

```php
$sw = Stopwatch::start();
$sw->lap('step-1');
$sw->lap('step-2');
$sw->lap('step-3');
$result = $sw->stop();

$stats = $result->stats();
echo $stats->mean();              // Mean lap duration in ms
echo $stats->median();            // Median lap duration in ms
echo $stats->p95();               // 95th percentile
echo $stats->standardDeviation(); // Standard deviation
```

## API

| Method | Returns | Description |
|--------|---------|-------------|
| `Stopwatch::start(?string $name)` | `RunningStopwatch` | Start a new stopwatch with an optional name |
| `Stopwatch::measure(callable $fn)` | `MeasureResult` | Measure execution time and memory of a callable |
| `Stopwatch::measureWithResult(callable $fn)` | `array{result, measure}` | Measure while preserving the return value |
| `Stopwatch::benchmark(array $callables, int $iterations)` | `BenchmarkResult` | Run each callable N times and compare performance |
| `RunningStopwatch->lap(?string $name)` | `self` | Record a lap with an optional name |
| `RunningStopwatch->child(string $name)` | `RunningStopwatch` | Create a nested child stopwatch |
| `RunningStopwatch->stop()` | `StopwatchResult` | Stop the timer and return results |
| `RunningStopwatch->pause()` | `void` | Pause timing without stopping the stopwatch |
| `RunningStopwatch->resume()` | `void` | Resume timing after a pause |
| `RunningStopwatch->getElapsedSoFar()` | `float` | Get elapsed seconds without stopping |
| `RunningStopwatch->elapsed()` | `float` | Get elapsed milliseconds while still running |
| `RunningStopwatch->isRunning()` | `bool` | Check if the stopwatch is still active |
| `RunningStopwatch->isPaused()` | `bool` | Check if the stopwatch is currently paused |
| `StopwatchResult->report()` | `string` | Generate a formatted report with all laps |
| `StopwatchResult->children()` | `array<StopwatchResult>` | Get child stopwatch results |
| `StopwatchResult->stats()` | `StopwatchStats` | Get statistical analysis of lap durations |

### Value Objects

**StopwatchResult** — `duration` (float, ms), `durationFormatted` (string), `memory` (int, bytes), `memoryFormatted` (string), `peakMemory` (int, bytes), `laps` (array), `name` (?string), `children` (array)

**MeasureResult** — `duration` (float, ms), `durationFormatted` (string), `memory` (int, bytes), `memoryFormatted` (string)

**Lap** — `name` (?string), `duration` (float, ms), `cumulativeDuration` (float, ms)

**BenchmarkResult** — `results()` (array of BenchmarkEntry), `report()` (string)

**BenchmarkEntry** — `mean` (float, ms), `median` (float, ms), `min` (float, ms), `max` (float, ms), `iterations` (int)

**StopwatchStats** — `mean()`, `median()`, `min()`, `max()`, `p95()`, `p99()`, `standardDeviation()` (all float, ms)

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
```

## Support

If you find this package useful, consider giving it a star on GitHub — it helps motivate continued maintenance and development.

[![LinkedIn](https://img.shields.io/badge/Philip%20Rehberger-LinkedIn-0A66C2?logo=linkedin)](https://www.linkedin.com/in/philiprehberger)
[![More packages](https://img.shields.io/badge/more-open%20source%20packages-blue)](https://philiprehberger.com/open-source-packages)

## License

[MIT](LICENSE)
