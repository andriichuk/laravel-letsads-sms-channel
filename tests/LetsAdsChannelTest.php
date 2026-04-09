<?php

use Andriichuk\LetsAdsSmsChannel\LetsAdsChannel;
use Andriichuk\LetsAdsSmsChannel\LetsAdsClient;
use Andriichuk\LetsAdsSmsChannel\SendSmsResponse;
use Andriichuk\LetsAdsSmsChannel\Sms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;

it('sends sms via LetsAdsChannel for notifiable model', function () {
    $client = $this->createMock(LetsAdsClient::class);

    $client->expects($this->once())
        ->method('sendSms')
        ->with($this->callback(function (array $params): bool {
            expect($params)->toMatchArray([
                'text' => 'Test message',
                'phone' => '+123456789',
            ]);

            return true;
        }))
        ->willReturn(new SendSmsResponse('Complete', 'queued', ['1']));

    $channel = new LetsAdsChannel($client);

    $notifiable = new class extends Model
    {
        use Notifiable;

        public string $phone = '+123456789';

        public function routeNotificationForLetsAds(): string
        {
            return $this->phone;
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['letsads'];
        }

        public function toLetsAds($notifiable): Sms
        {
            return new Sms(text: 'Test message');
        }
    };

    $channel->send($notifiable, $notification);
});

it('resolves phone using full channel class name', function () {
    $client = $this->createMock(LetsAdsClient::class);

    $client->expects($this->once())
        ->method('sendSms')
        ->with($this->callback(function (array $params): bool {
            expect($params)->toMatchArray([
                'text' => 'Class based message',
                'phone' => '+987654321',
            ]);

            return true;
        }))
        ->willReturn(new SendSmsResponse('Complete', 'queued', ['1']));

    $channel = new LetsAdsChannel($client);

    $notifiable = new class extends Model
    {
        use Notifiable;

        public function routeNotificationFor($driver): ?string
        {
            return match ($driver) {
                'letsads' => null,
                LetsAdsChannel::class => '+987654321',
                default => null,
            };
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return [LetsAdsChannel::class];
        }

        public function toLetsAds($notifiable): Sms
        {
            return new Sms(text: 'Class based message');
        }
    };

    $channel->send($notifiable, $notification);
});

it('sends sms for anonymous notifiable route', function () {
    $client = $this->createMock(LetsAdsClient::class);

    $client->expects($this->once())
        ->method('sendSms')
        ->with($this->callback(function (array $params): bool {
            expect($params)->toMatchArray([
                'text' => 'Anonymous message',
                'phone' => '+380991112233',
            ]);

            return true;
        }))
        ->willReturn(new SendSmsResponse('Complete', 'queued', ['1']));

    $this->app->instance(LetsAdsClient::class, $client);

    NotificationFacade::route('letsads', '+380991112233')
        ->notify(new class extends Notification
        {
            public function via($notifiable): array
            {
                return ['letsads'];
            }

            public function toLetsAds($notifiable): Sms
            {
                return new Sms(text: 'Anonymous message');
            }
        });
});

it('throws when notification does not implement toLetsAds', function () {
    $channel = new LetsAdsChannel($this->createMock(LetsAdsClient::class));

    $notifiable = new class extends Model
    {
        use Notifiable;
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['letsads'];
        }
    };

    $channel->send($notifiable, $notification);
})->throws(InvalidArgumentException::class, 'Notification must implement toLetsAds() method.');

it('throws when toLetsAds does not return Sms instance', function () {
    $channel = new LetsAdsChannel($this->createMock(LetsAdsClient::class));

    $notifiable = new class extends Model
    {
        use Notifiable;

        public function routeNotificationForLetsAds(): string
        {
            return '+123456789';
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['letsads'];
        }

        public function toLetsAds($notifiable): string
        {
            return 'not an Sms instance';
        }
    };

    $channel->send($notifiable, $notification);
})->throws(InvalidArgumentException::class, 'Notification::toLetsAds() must return an instance of '.Sms::class.'.');

it('throws when phone cannot be resolved from notifiable', function () {
    $channel = new LetsAdsChannel($this->createMock(LetsAdsClient::class));

    $notifiable = new class extends Model
    {
        use Notifiable;

        public function routeNotificationForLetsAds(): string
        {
            return '';
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['letsads'];
        }

        public function toLetsAds($notifiable): Sms
        {
            return new Sms(text: 'Test');
        }
    };

    $channel->send($notifiable, $notification);
})->throws(InvalidArgumentException::class, 'Could not determine recipient phone number for LetsAds SMS notification.');

it('logs response body when configured', function () {
    config()->set('services.letsads.log_response', true);

    $client = $this->createMock(LetsAdsClient::class);
    $response = new SendSmsResponse(
        name: 'Complete',
        description: '1 messages put into queue',
        smsIds: ['633217'],
    );

    $client->expects($this->once())
        ->method('sendSms')
        ->willReturn($response);

    Log::shouldReceive('info')
        ->once()
        ->with('LetsAds SMS response', [
            'response' => [
                'name' => 'Complete',
                'description' => '1 messages put into queue',
                'sms_ids' => ['633217'],
            ],
        ]);

    $channel = new LetsAdsChannel($client);

    $notifiable = new class extends Model
    {
        use Notifiable;

        public function routeNotificationForLetsAds(): string
        {
            return '+123456789';
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['letsads'];
        }

        public function toLetsAds($notifiable): Sms
        {
            return new Sms(text: 'Test message');
        }
    };

    $channel->send($notifiable, $notification);
});
