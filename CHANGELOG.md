# Changelog

All notable changes to `usercheck/usercheck-laravel` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.6.0] - 2026-04-09

### Added
- Laravel 13 support (`illuminate/support: ^13.0`, `orchestra/testbench: ^11.0`).
- CI now installs each supported Laravel version explicitly via a matrix
  (PHP 8.2/8.3/8.4 × Laravel 11/12/13), so the green check actually proves
  every supported combination works rather than only whatever a lockfile
  happened to pin.

### Changed
- Minimum PHP raised from 8.0 to 8.2 (Laravel 11+ already required PHP 8.2).
- Bumped dev tooling to versions that support Laravel 12 and 13: larastan
  `^3.0`, pest `^4.0`, pest-plugin-laravel `^4.0`, pest-plugin-arch `^4.0`,
  phpstan deprecation/phpunit rules `^2.0`.
- Lint and static analysis now run as a separate `quality` job in parallel
  with the test matrix.

### Removed
- Stopped committing `composer.lock` (libraries shouldn't ship one). Each CI
  cell resolves dependencies fresh against the constraints in `composer.json`.

## [0.5.0] - 2025-05-27

### Added
- Laravel 12 support (`illuminate/support: ^12.0`).

## [0.4.0] - 2025-03-21

### Added
- `block_spam` flag — fail validation when the domain is marked as spam by
  the UserCheck API.

## [0.3.0] - 2025-01-21

### Added
- `block_relay_domain` flag — fail validation when the email is from an
  email forwarding/relay service.

## [0.2.0] - 2024-12-03

### Added
- `block_blocklisted` flag — fail validation when the domain is on your
  custom blocklist (paid plans only).

### Changed
- Renamed package from `usercheckhq/laravel` to `usercheck/usercheck-laravel`
  on Packagist.

## [0.1.0] - 2024-09-23

### Fixed
- 400 responses from the API are now reported as a generic validation error
  instead of throwing.

## [0.0.1] - 2024-09-19

### Added
- Initial release.
- `usercheck` validation rule with `block_disposable`, `block_no_mx`,
  `block_public_domain`, and `domain_only` flags.
- `UserCheck` facade for direct API calls (`validateEmail`, `validateDomain`).
- English translations for all error messages.

[0.6.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.5.0...v0.6.0
[0.5.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/usercheckhq/usercheck-laravel/compare/v0.0.1...v0.1.0
[0.0.1]: https://github.com/usercheckhq/usercheck-laravel/releases/tag/v0.0.1
