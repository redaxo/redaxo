# Changelog

## [1.1.3] - 2018-03-27

### Updated

- `composer.lock` tracks `symfony/yaml` v4.0.6

### Fixed

- Added input args validation to check for multiple files and updated README
  (fixes #7)
- Improved syntax in README docs (resolves #4)

## [1.1.2] - 2017-12-07

### Added

- Added support for Symfony 4 YAML component
- New CLI option for displaying application version
- README documentation now includes 'dependencies' badge

### Updated

- Refactored custom 'UsageException' class into standalone file
- Updated application descriptions to emphasise 'compact' design of the application
- composer update now tracks latest Symfony 4 YAML in local sandbox (composer.lock)

### Fixed

- Fix to accommodate changes in the Yaml::parse method introduced in v3

## [1.1.1] - 2016-11-11

### Added

- Switched to full array notation, allowing legacy PHP support
- composer update tracks latest Symfony 3 YAML in local sandbox (composer.lock)

## [1.1.0] - 2016-09-12

### Added

- Support for reading from stdin
- box.json manifest for building PHAR binaries
- Enabled support for Symfony 3 YAML component

## [1.0.0] - 2016-03-02

### Added

- Initial release
