<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use Illuminate\Contracts\Support\Arrayable;

final readonly class SendSmsResponse implements Arrayable
{
    /**
     * @param  list<string>  $smsIds
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $smsIds = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'sms_ids' => $this->smsIds,
        ];
    }
}
