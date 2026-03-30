# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and the project follows Semantic Versioning.

## [Unreleased]

### Added

- Detailed library guide with architecture map, canonical sections, DTO API and Mermaid diagrams.
- Psalm execution in CI alongside PHP CS Fixer, PHPStan and PHPUnit.
- Deptrac architecture analysis and dedicated deptrac configuration.
- Infection mutation testing setup and scheduled mutation workflow.
- Coverage threshold check based on Clover XML report.

### Changed

- Tightened PHPStan, Psalm and PHP CS Fixer configuration based on conventions used in mature PHP libraries.
- Aligned parser, XML loader, collections and tests with stricter static analysis rules.
- Added project cache directory ignore rules for local tooling output.
- Shifted documentation and typed-getter examples toward explicit collection access via `get()`.

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
