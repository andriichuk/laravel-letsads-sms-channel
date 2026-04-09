<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final readonly class LetsAdsChannel
{
    public function __construct(
        private LetsAdsClient $letsAdsClient,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toLetsAds')) {
            throw new InvalidArgumentException(
                'Notification must implement toLetsAds() method.'
            );
        }

        $message = $notification->toLetsAds($notifiable);

        if (! $message instanceof Sms) {
            throw new InvalidArgumentException(
                'Notification::toLetsAds() must return an instance of '.Sms::class.'.'
            );
        }

        $data = $message->toArray();

        if (($data['phone'] ?? '') === '') {
            $data['phone'] = $this->resolvePhone($notifiable);
        }

        if ($data['phone'] === '') {
            throw new InvalidArgumentException(
                'Could not determine recipient phone number for LetsAds SMS notification.'
            );
        }

        $response = $this->letsAdsClient->sendSms($data);

        if ((bool) config('services.letsads.log_response', false)) {
            Log::info('LetsAds SMS response', [
                'response' => $response->toArray(),
            ]);
        }
    }

    private function resolvePhone(object $notifiable): string
    {
        if (! method_exists($notifiable, 'routeNotificationFor')) {
            return (string) $notifiable;
        }

        $phone = $notifiable->routeNotificationFor('letsads');

        if ($phone === null) {
            $phone = $notifiable->routeNotificationFor(self::class);
        }

        return (string) ($phone ?? '');
    }
}
