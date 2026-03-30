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
- Dedicated coverage-focused tests for immutable collections, parser units, DTO accessors and edge-case normalization branches.
- Real fixture parity tests for paired legacy/modern ATON reports.
- Parse diagnostics API on `Report` for unknown structures and unexpected keys.
- Dedicated diagnostics for synthetic legacy-compatible sections derived from modern XML.
- Decimal string math helper for aggregate calculations without float precision loss.

### Changed

- Tightened PHPStan, Psalm and PHP CS Fixer configuration based on conventions used in mature PHP libraries.
- Aligned parser, XML loader, collections and tests with stricter static analysis rules.
- Added project cache directory ignore rules for local tooling output.
- Shifted documentation and typed-getter examples toward explicit collection access via `get()`.
- Removed unreachable parser guard branches around DOM internals instead of preserving dead code for tooling.
- Reached `100%` local coverage for lines, methods and classes.
- Split modern `PortfolioMoney` records into canonical `MoneyOnDate`, `MoneyOnDate_MarketPrc` and `MoneyOnDate_ByOperPlace` sections.
- Normalized strictly symmetric `MoneyInOut_io` duplicate pairs to a legacy-compatible single-row result.
- Added derived legacy-compatibility sections for modern reports:
  - `MoneyOnDate_single`
  - `StockOnDate_Exg_Sum`
- Added known-schema checks for old/new report structures with diagnostics for unknown sections, sources and fields.
- Switched `StockOnDate_Exg_Sum` to deterministic aggregation over `StockOnDate_Exg` rows instead of a single-row-only compatibility case.
- Extended real fixture parity tests to assert that paired old/new fixtures differ only in explicitly allowed synthetic or modern-only sections.

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
