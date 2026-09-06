# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

## [0.0.12](https://github.com/eufelipemateus/laravel-iptv-cms/compare/2026.07.24+16...v0.0.12) (2026-08-10)

### Features

- add configurable, scheduled resets for store/demo data, including deterministic test-data seeding ([#35](https://github.com/eufelipemateus/laravel-iptv-cms/pull/35))
- replace legacy customer access hashes with secure authentication tokens and harden private playlist access ([#35](https://github.com/eufelipemateus/laravel-iptv-cms/pull/35))
- validate stream URLs and protect outbound requests against SSRF and unsafe hosts ([#35](https://github.com/eufelipemateus/laravel-iptv-cms/pull/35))

### Maintenance

- update the deployment and GitHub release workflow ([cc030e5](https://github.com/eufelipemateus/laravel-iptv-cms/commit/cc030e5a7ff7b8779d72ef431c174cbb61801fc9))

## [2026.07.24+16](https://github.com/eufelipemateus/laravel-iptv-cms/compare/2026.07.24+15...2026.07.24+16) (2026-07-24)

### Bug Fixes

- assign a stable, shorter name to the customer-plan composite unique constraint ([9f306c6](https://github.com/eufelipemateus/laravel-iptv-cms/commit/9f306c6f66c6a36f4eac6ce50d959379890f93c5))

## [2026.07.24+15](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.11...2026.07.24+15) (2026-07-24)

### Changed

- move the IPTV core, channel, customer, and payment modules into the main application ([#22](https://github.com/eufelipemateus/laravel-iptv-cms/pull/22), [#23](https://github.com/eufelipemateus/laravel-iptv-cms/pull/23))
- reorganize business operations into actions and request validators and simplify controllers ([#29](https://github.com/eufelipemateus/laravel-iptv-cms/pull/29))
- migrate the frontend build from Laravel Mix to Vite and npm

### Tests

- add CI workflows and automated coverage for CRUD flows, middleware, playlists, models, relationships, migrations, and invoice calculations ([#23](https://github.com/eufelipemateus/laravel-iptv-cms/pull/23), [#29](https://github.com/eufelipemateus/laravel-iptv-cms/pull/29))

## [0.0.11](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.10...v0.0.11) (2025-04-27)

### Changed

- migrate legacy packages into the repository and upgrade the application to Laravel 12 ([#17](https://github.com/eufelipemateus/laravel-iptv-cms/pull/17), [#18](https://github.com/eufelipemateus/laravel-iptv-cms/pull/18))

### Bug Fixes

- correct configuration queries and locale input handling ([#19](https://github.com/eufelipemateus/laravel-iptv-cms/pull/19), [#20](https://github.com/eufelipemateus/laravel-iptv-cms/pull/20))

## [0.0.10](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.9...v0.0.10) (2023-04-06)

### Bug Fixes

- update the channel and customer modules to correct CDN update and deletion behavior

## [0.0.9](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.8...v0.0.9) (2023-02-12)

### Changed

- update the IPTV customer module dependency ([#13](https://github.com/eufelipemateus/laravel-iptv-cms/pull/13))

## [0.0.8](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.7...v0.0.8) (2023-02-06)

### Bug Fixes

- correct the application's CORS configuration ([#11](https://github.com/eufelipemateus/laravel-iptv-cms/pull/11))

## [0.0.7](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.6...v0.0.7) (2022-10-12)

### Changed

- upgrade the application to Laravel 9 ([#10](https://github.com/eufelipemateus/laravel-iptv-cms/pull/10))
- update the installation documentation ([#7](https://github.com/eufelipemateus/laravel-iptv-cms/pull/7))

## [0.0.6](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.5...v0.0.6) (2022-08-25)

### Bug Fixes

- import the `URL` class correctly ([#6](https://github.com/eufelipemateus/laravel-iptv-cms/pull/6))

## [0.0.5](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.4...v0.0.5) (2022-08-25)

### Features

- add the PayPal payment gateway ([#5](https://github.com/eufelipemateus/laravel-iptv-cms/pull/5))

### Bug Fixes

- correct customer module behavior

## [0.0.4](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.3...v0.0.4) (2022-05-31)

### Changed

- update project dependencies ([#4](https://github.com/eufelipemateus/laravel-iptv-cms/pull/4))

### Features

- force HTTPS URLs in production ([#3](https://github.com/eufelipemateus/laravel-iptv-cms/pull/3))

## [0.0.3](https://github.com/eufelipemateus/laravel-iptv-cms/compare/v0.0.2...v0.0.3) (2022-05-29)

### Bug Fixes

- remove Version from composer.json file ([146b94a](https://github.com/eufelipemateus/laravel-iptv-cms/commit/146b94a4b3096b3154dc0e54cdb0df7cc01bfc7d))
