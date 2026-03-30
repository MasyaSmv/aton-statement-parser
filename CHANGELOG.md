# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and the project follows Semantic Versioning.

## [Unreleased]

## [0.1.0] - 2026-03-30

### Added

- Multi-format report parsing for legacy BIS and modern XML statements.
- Immutable canonical DTO layer for common report sections.
- Dedicated mapper interfaces for DTO services.
- Local and fixture-based coverage for canonical DTO mapping.
- Release workflow for tags matching `v*.*.*`.

### Changed

- Unified old and new statement formats into one canonical report model.
- Improved XML loading for large real-world reports.
- Extended README with DTO API, versioning and release process details.
