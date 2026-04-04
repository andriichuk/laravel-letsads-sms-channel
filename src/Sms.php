<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use Illuminate\Contracts\Support\Arrayable;

final readonly class Sms implements Arrayable
{
    public function __construct(
        public string $text,
        public ?string $phone = null,
        public ?string $from = null,
    ) {}

    /**
     * @return array{text: string, phone: string|null, from?: string}
     */
    public function toArray(): array
    {
        $data = [
            'text' => $this->text,
            'phone' => $this->phone,
        ];

        if ($this->from !== null) {
            $data['from'] = $this->from;
        }

        return $data;
    }
}
