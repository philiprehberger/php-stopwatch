# Changelog

All notable changes to `php-stopwatch` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-03-27

### Added
- Nested stopwatches via `RunningStopwatch::child()` for hierarchical timing
- `Stopwatch::benchmark()` for comparing performance of multiple callables
- `StopwatchResult::stats()` for statistical analysis (mean, median, percentiles, std deviation)

## [1.1.2] - 2026-03-23

### Changed
- Standardize README requirements format per template guide

## [1.1.1] - 2026-03-23

### Fixed
- Remove decorative dividers from README for template compliance

## [1.1.0] - 2026-03-22

### Added
- `pause()` and `resume()` methods on `RunningStopwatch` for pausing without stopping
- `getElapsedSoFar()` method to read elapsed time without stopping the stopwatch

## [1.0.4] - 2026-03-20

### Added
- Expanded test suite with value object and formatter edge case tests

## [1.0.3] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

## [1.0.1] - 2026-03-15

### Changed
- Standardize README badges

## [1.0.0] - 2026-03-15

### Added
- `Stopwatch::start()` for manual start/stop timing with optional name
- `Stopwatch::measure()` for measuring callable execution time and memory
- `Stopwatch::measureWithResult()` for measuring while preserving return value
- Lap tracking with named laps and cumulative durations
- Memory usage and peak memory measurement
- Human-readable formatting for durations (ms, s, m) and byte sizes (B, KB, MB, GB)
- `StopwatchResult::report()` for formatted output including all laps
