# Changelog
All notable changes to this project will be documented in this file.

## 1.0.4
- remove pageTitle on first page/ front page inside root

## 1.0.3

### Added
- initially set `huh.head.tag.title` to `{{page::pageTitle}} - {{page::rootPageTitle}}` and replace inserttag when tag is rendered
- always run `\Contao\StringUtil::stripInsertTags` and `\Contao\Controller::replaceInsertTags` when tag is rendered

## 1.0.2

### Added
- `<link rel="canonical" href="">` to all pages, without query string

## 1.0.1

### Changed
- refactored namespaces

### Added
- `<link rel="prev" href="">`
- `<link rel="next" href="">`

## 1.0.0

- initial version
