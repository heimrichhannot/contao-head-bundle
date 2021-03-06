# Changelog
All notable changes to this project will be documented in this file.

## [1.3.1] - 2020-10-22
- fixed quote escaping for link tags

## [1.3.0] - 2020-07-17
- added support for rootfallback palette of contao 4.9
- rootpage twitterSite filed now evaluated

## [1.2.5] - 2020-05-26
- updated service definitions for better symfony 4 compatibility

## [1.2.4] - 2019-11-14
- updated service definitions for better symfony 4 compatibility
- removed unused code

## [1.2.3] - 2019-07-23

### Changed
- added licence file

## [1.2.2] - 2019-07-16

### Changed
- updated readme
- some code enhancements

## [1.2.1] - 2019-04-23

### Fixed
- compatibility with symfony 4 (updated framework bundle dependency)

## [1.2.0] - 2019-02-28

### Changed
- `$this->meta` in `fe_page` is now a function that can be passed an array `$skip` head bundle tag services 

## [1.1.9] - 2019-02-27

### Fixed
- `AbstractTag::hasContent()` now uses `empty()` in order to properly check against null, 0, false, ''

## [1.1.8] - 2018-11-15

### Fixed
- quote escaping for all html attributes
- codestyle

## [1.1.7] - 2018-10-19

### Fixed
- charset meta tag -> now contains the correct syntax

## [1.1.6] - 2018-07-26

### Fixed
- just add selected get params to canonical url

## [1.1.5] - 2018-07-26

### Fixed
- add the complete url with params

## [1.1.4] - 2018-07-16

### Fixed
- missing quote escaping for all html attributes

## [1.1.3] - 2018-04-09

### Changed
- `TwitterDescription` and `OGDescription` should not contain html tags, and truncated length to max `320` characters 

## [1.1.2] - 2018-03-26

### Changed
- load after `heimrichhannot/contao-modal`

### Added
- CompilerPass added to provide container parameter `huh.head.tags` that contains all available tag services 

## [1.1.1] - 2018-03-12

### Added
- testcases

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
