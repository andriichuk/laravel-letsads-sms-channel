# Changelog

All notable changes to `laravel-letsads-sms-channel` will be documented in this file.

## Unreleased

- Initial release of the LetsAds SMS notification channel for Laravel
- Adds `LetsAdsChannel` for sending SMS via the [LetsAds XML API](https://letsads.com/api-sms-povidomlennia)
- Provides `Sms` value object and `LetsAdsClient` HTTP client

If you are migrating from another SMS channel package, use `toLetsAds()`, `routeNotificationForLetsAds()`, and the `letsads` notification driver with the credentials documented in the readme.
