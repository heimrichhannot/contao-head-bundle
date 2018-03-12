# Changelog
All notable changes to this project will be documented in this file.

## [1.1.0] - 2018-03-12

### Added
- dependency to heimrichhannot/contao-utils-bundle 2.0

## [1.0.10] - 2018-03-12

### Added
- facebook (OG) and twitter default meta data
- default image for facebook and twitter meta data

### Changed
- restored contao 4.5.* in travis.yml

### Removed
- PHP 5.6 from allowed php versions

## [1.0.9] - 2018-03-12

### Changed
- excluded contao 4.5.* from travis.yml

## [1.0.8] - 2018-03-08

### Added
- new tag

## [1.0.7] - 2018-03-08

### Added
- testscases

## [1.0.6] - 2018-03-08

### Added
- tests

## [1.0.5] - 2018-02-16

### Added
- symfony 4 support, make services `public` where required, otherwise used dependency injection
- php `5.6` and `7.2` support

## [1.0.4] - 2017-11-06

### Changed

- remove pageTitle on first page/ front page inside root

## [1.0.3] - 2017-09-18

### Added
- initially set `huh.head.tag.title` to `{{page::pageTitle}} - {{page::rootPageTitle}}` and replace inserttag when tag is rendered
- always run `\Contao\StringUtil::stripInsertTags` and `\Contao\Controller::replaceInsertTags` when tag is rendered

## [1.0.2] - 2017-09-13

### Added
- `<link rel="canonical" href="">` to all pages, without query string

## [1.0.1] - 2017-09-13

### Changed
- refactored namespaces

### Added
- `<link rel="prev" href="">`
- `<link rel="next" href="">`

## [1.0.0] - 2017-09-12

- initial version
