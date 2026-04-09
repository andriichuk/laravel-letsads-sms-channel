# LetsAds SMS Notification Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/andriichuk/laravel-letsads-sms-channel.svg?style=flat-square)](https://packagist.org/packages/andriichuk/laravel-letsads-sms-channel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/andriichuk/laravel-letsads-sms-channel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/andriichuk/laravel-letsads-sms-channel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/andriichuk/laravel-letsads-sms-channel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/andriichuk/laravel-letsads-sms-channel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/andriichuk/laravel-letsads-sms-channel.svg?style=flat-square)](https://packagist.org/packages/andriichuk/laravel-letsads-sms-channel)

This package makes it easy to send SMS notifications using [LetsAds](https://letsads.com/api-sms-povidomlennia) from your Laravel application, using Laravel's built-in notification system.

Sending an SMS to a user becomes as simple as using:

```php
$user->notify(new Invitation());
```

## Installation

You can install the package via composer:

```bash
composer require andriichuk/laravel-letsads-sms-channel
```

The service provider will be auto-discovered by Laravel.

### Setting up the LetsAds service

Add your LetsAds SMS credentials to the `services.php` config file:

```php
// config/services.php

return [
    // ...

    'letsads' => [
        'login' => env('LETSADS_SMS_LOGIN'),
        'password' => env('LETSADS_SMS_PASSWORD'),
        'from' => env('LETSADS_SMS_FROM'),
        'log_response' => env('LETSADS_SMS_LOG_RESPONSE', false),
    ],
];
```

`login` is the phone number of your LetsAds account (digits, e.g. `380501234567`). `from` is your registered sender name. API access must be enabled in your LetsAds account.

Then add the corresponding environment variables to your `.env`:

```bash
LETSADS_SMS_LOGIN="380501234567"
LETSADS_SMS_PASSWORD="your-api-password"
LETSADS_SMS_FROM="Your Sender Name"
LETSADS_SMS_LOG_RESPONSE=false
```

## Usage

### Notifiable model

In your notifiable model (typically `User`), add the `routeNotificationForLetsAds` method that returns a full mobile number including country code:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForLetsAds(): string
    {
        return $this->phone; // e.g. +380991112233
    }
}
```

### Notification class

Within your notification, add the LetsAds channel to the `via` method and implement `toLetsAds` to build the SMS message:

```php
use Andriichuk\LetsAdsSmsChannel\LetsAdsChannel;
use Andriichuk\LetsAdsSmsChannel\Sms;
use Illuminate\Notifications\Notification;

class Invitation extends Notification
{
    public function via($notifiable): array
    {
        return [LetsAdsChannel::class];
    }

    public function toLetsAds($notifiable): Sms
    {
        return new Sms(
            text: 'You have been invited!',
        );
    }
}
```

Now you can send an SMS notification to a user:

```php
$user->notify(new Invitation());
```

### Anonymous notifications

You can also send SMS messages to phone numbers that are not associated with a notifiable model:

```php
use Illuminate\Support\Facades\Notification;

Notification::route(LetsAdsChannel::class, '+380991112233')
    ->notify(new Invitation());
```

The channel resolves the recipient phone from the notifiable (via `routeNotificationFor(LetsAdsChannel::class)` or the notifiable’s string form), so your `toLetsAds` method can stay the same and omit the `phone` argument.

### Available message options

The `Sms` value object supports:

- `text` (string) – the message body.
- `phone` (string, optional) – recipient phone number including country code; if omitted, the channel resolves it from the notifiable.
- `from` (string, optional) – sender name for this message only; if omitted, the default from `config/services.php` is used.

## Testing

Run the test suite with:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Serhii Andriichuk](https://github.com/andriichuk)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
