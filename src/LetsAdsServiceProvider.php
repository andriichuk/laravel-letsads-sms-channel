<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

final class LetsAdsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(LetsAdsClient::class, static function (): LetsAdsClient {
            return new LetsAdsClient(
                (string) config('services.letsads.login'),
                (string) config('services.letsads.password'),
                (string) config('services.letsads.from'),
            );
        });
    }

    public function provides(): array
    {
        return [LetsAdsClient::class];
    }
}
