# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog and the project follows Semantic Versioning.

## [Unreleased]

### Added

- Multi-format report parsing for legacy BIS and modern XML statements.
- Immutable canonical DTO layer for common report sections.
- Dedicated mapper interfaces for DTO services.

### Changed

- Unified old and new statement formats into one canonical report model.
- Improved XML loading for large real-world reports.

## [0.1.0] - 2026-03-30

### Added

- Initial public package structure.
- Core report parsing API.
- Typed row getters and normalizers.

