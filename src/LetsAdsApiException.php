<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use RuntimeException;

final class LetsAdsApiException extends RuntimeException
{
    public static function fromResponse(string $name, string $description): self
    {
        return new self("LetsAds API error: {$name} - {$description}");
    }
}
