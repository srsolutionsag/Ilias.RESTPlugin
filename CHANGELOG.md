# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)

## [1.10.1] - 2020-09-10
### Fixed
- Fix issues with user session on ILIAS 6

### Removed
- ILIAS 5.2 support

## [1.10.0] - 2020-08-18
### Added
- New object route which allows to fetch a single object by refId.

### Fixed
- Children of objects which contains a learning sequence no longer errors.
- Learning module ZIPs now have the correct size.
- Plugin max ILIAS version constraint

## [1.9.1] - 2020-05-26
### Added
- configuration of REST clients without having to use the API
- support for learning modules (htlm, sahs)

### Fixed
- eBook note sync now accepts strings with newline characters.

## [1.9.0] - 2020-04-02
### Added
- eBook cover route

## [1.8.4] - 2019-12-05
### Added
- ILIASAPP-645 (version 1) farbwahl
- route v3 for theme colors
- setting theme colors in the configuration page of the plugin
- route v1 'routesAccess' for testing purposes

## [1.8.3] - 2019-11-22
### Added
- RP001 & RP002: Lernforttschritte abholen & hochschreiben
- route v3 for files
- checking access-rights for files

### Fixed
- array definition in RESTLib.php

# Template
## [v.v.v] - yyyy-mm-dd
### Added
- ...
### Changed
- ...
### Fixed
- ...
### Removed
- ...