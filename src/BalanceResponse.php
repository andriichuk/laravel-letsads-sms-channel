<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

final readonly class BalanceResponse
{
    public function __construct(
        public string $name,
        public string $description,
        public string $currency,
    ) {}
}
