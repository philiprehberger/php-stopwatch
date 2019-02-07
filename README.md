# PHP Stopwatch

[![Tests](https://github.com/philiprehberger/php-stopwatch/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/php-stopwatch/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/php-stopwatch.svg)](https://packagist.org/packages/philiprehberger/php-stopwatch)
[![License](https://img.shields.io/github/license/philiprehberger/php-stopwatch)](LICENSE)

Precise code execution timer with lap tracking and memory measurement.


## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ^8.2    |

No external dependencies required.


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


## API

| Method | Returns | Description |
|--------|---------|-------------|
| `Stopwatch::start(?string $name)` | `RunningStopwatch` | Start a new stopwatch with an optional name |
| `Stopwatch::measure(callable $fn)` | `MeasureResult` | Measure execution time and memory of a callable |
| `Stopwatch::measureWithResult(callable $fn)` | `array{result, measure}` | Measure while preserving the return value |
| `RunningStopwatch->lap(?string $name)` | `self` | Record a lap with an optional name |
| `RunningStopwatch->stop()` | `StopwatchResult` | Stop the timer and return results |
| `RunningStopwatch->pause()` | `void` | Pause timing without stopping the stopwatch |
| `RunningStopwatch->resume()` | `void` | Resume timing after a pause |
| `RunningStopwatch->getElapsedSoFar()` | `float` | Get elapsed seconds without stopping |
| `RunningStopwatch->elapsed()` | `float` | Get elapsed milliseconds while still running |
| `RunningStopwatch->isRunning()` | `bool` | Check if the stopwatch is still active |
| `RunningStopwatch->isPaused()` | `bool` | Check if the stopwatch is currently paused |
| `StopwatchResult->report()` | `string` | Generate a formatted report with all laps |

### Value Objects

**StopwatchResult** — `duration` (float, ms), `durationFormatted` (string), `memory` (int, bytes), `memoryFormatted` (string), `peakMemory` (int, bytes), `laps` (array), `name` (?string)

**MeasureResult** — `duration` (float, ms), `durationFormatted` (string), `memory` (int, bytes), `memoryFormatted` (string)

**Lap** — `name` (?string), `duration` (float, ms), `cumulativeDuration` (float, ms)


## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT
