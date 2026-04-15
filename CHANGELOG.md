# Changelog

All notable changes to `laravel-letsads-sms-channel` will be documented in this file.

## Unreleased

## 0.1.4 - 2026-04-15

- Require `illuminate/contracts` ^13.5 for Laravel 13. The PHP 8.4 error *Cannot bind an instance to a static closure* in `Illuminate\Support\Manager::extend` is fixed only from **Laravel v13.5.0** onward; 13.0 through 13.4 still use `Closure::bindTo()` in that method.
- README: document Laravel 13.5+ for the v13 series.

## 0.1.3 - 2026-04-15

- Require `illuminate/contracts` ^13.1 for Laravel 13 so `prefer-lowest` does not install 13.0.x, which breaks on PHP 8.4 during `testbench package:discover` (`Illuminate\Support\Manager::extend` and static closures).
- CI: pass `--dev` to `composer require` so `orchestra/testbench` remains in `require-dev`.
- README: document Laravel 13.1+ as the minimum for the v13 series.

## 0.1.2 - 2026-04-15

- Add Laravel 13 support (`illuminate/contracts` ^13.0, Orchestra Testbench 11.x, CI matrix, PHPStan workflow alignment).

## 0.1.0

- Initial release of the LetsAds SMS notification channel for Laravel
- Adds `LetsAdsChannel` for sending SMS via the [LetsAds XML API](https://letsads.com/api-sms-povidomlennia)
- Provides `Sms` value object and `LetsAdsClient` HTTP client

If you are migrating from another SMS channel package, use `toLetsAds()`, `routeNotificationForLetsAds()`, and the `letsads` notification driver with the credentials documented in the readme.
