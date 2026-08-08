# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

> **Versioning note (2026-08-08).** This package was renumbered down to `0.x` to
> signal that its API is still settling. Tags were re-cut at the same commits:
> old `vN.m.p` became `v0.N.<ordinal-within-N>`. Under Composer, `^0.4.0` resolves
> to `>=0.4.0 <0.5.0`, so **every minor release may break** — which is the point.
> It will go to `1.0.0` when the consuming apps ship publicly.

## [Unreleased]

### Changed
- **BREAKING for anyone pinned to Laravel 11: `^11.0` dropped from every `illuminate/*` constraint** (now `^12.0|^13.0`), and `orchestra/testbench` narrowed to `^10.0|^11.0` (Testbench 9 *is* Laravel 11). The support was advertised but structurally untestable: this package requires `pestphp/pest ^4.0`, Pest 4 requires PHPUnit 12, and Testbench 9 caps at PHPUnit 11 — so a Laravel 11 test harness could never resolve here, and no consumer on Laravel 11 was ever verified. Advertising a range wider than the one we can run is a promise nobody was keeping
- **PHP floor lowered to `^8.2`** (was `^8.3`). A library's floor is a compatibility promise to consumers, not a statement about the machine it is developed on. This widens the supported range; no consumer on 8.3+ is affected
- Removed the PHP 8.3-only `#[\Override]` attribute from `src/` (10 usages across 7 classes). It is inert on PHP 8.2, so it silently stopped enforcing anything under the new floor while implying it still did

### Added
- `quality` now gates Rector (`@refactor:check`), which had been defined but never composed into the gate since the package was created
- PHPStan pins `phpVersion: 80200` so the declared floor is checked mechanically rather than aspirationally, plus `tmpDir: .phpstan.cache`

## [0.1.1] - 2026-06-10

### Fixed
- UUID coherence for user-id columns in `uuid7` mode: `annotated_by` and the tenant column are now created as `uuid` columns when `four_corners.id_type` is `uuid7` (previously always `unsignedBigInteger`)
- `DocumentAnnotation` no longer casts `annotated_by` to `integer` in `uuid7` mode (uuid strings were being mangled to `0`/truncated ints); the cast now follows the configured id type
- `AnnotationController` no longer force-casts the authenticated user id to `int`; the id is passed as a string in `uuid7` mode and cast to `int` only in `incrementing` mode

### Changed
- `AnnotationService::complete()` / `reject()`, `ProcessAnnotationJob`, `RejectionData`, and the `FourCorners` facade signatures now accept `int|string` for user ids (and `rejection_reason_id`)
- TypeScript `annotatedBy` type widened to `number | string | null`

## [0.1.0]

### Added
- Initial package setup
- Document annotation workflow with corner detection
- Perspective transformation using OpenCV.js
- Rejection workflow with categorized reasons
- Training data export for ML development
- Vue 3 components for annotation UI
- Queue-based processing with Laravel queues
- Event-driven architecture for consuming app integration
