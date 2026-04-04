<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use Illuminate\Support\Facades\Notification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LetsAdsSmsChannelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-letsads-sms-channel');
    }

    public function packageRegistered(): void
    {
        $this->app->register(LetsAdsServiceProvider::class);
    }

    public function packageBooted(): void
    {
        Notification::extend('letsads', static function ($app): LetsAdsChannel {
            return $app->make(LetsAdsChannel::class);
        });
    }
}
