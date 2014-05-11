# Changelog

All notable changes to `php-stopwatch` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-15

### Added
- `Stopwatch::start()` for manual start/stop timing with optional name
- `Stopwatch::measure()` for measuring callable execution time and memory
- `Stopwatch::measureWithResult()` for measuring while preserving return value
- Lap tracking with named laps and cumulative durations
- Memory usage and peak memory measurement
- Human-readable formatting for durations (ms, s, m) and byte sizes (B, KB, MB, GB)
- `StopwatchResult::report()` for formatted output including all laps
