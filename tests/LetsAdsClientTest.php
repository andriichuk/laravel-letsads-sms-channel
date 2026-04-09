<?php

use Andriichuk\LetsAdsSmsChannel\BalanceResponse;
use Andriichuk\LetsAdsSmsChannel\LetsAdsApiException;
use Andriichuk\LetsAdsSmsChannel\LetsAdsClient;
use Andriichuk\LetsAdsSmsChannel\SendSmsResponse;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

it('posts send SMS request with expected XML body', function () {
    $httpClient = $this->createMock(HttpClient::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')
        ->willReturn('<?xml version="1.0" encoding="UTF-8"?><response><name>Complete</name><description>2 messages put into queue</description><sms_id>633217</sms_id><sms_id>633218</sms_id></response>');

    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('post')
        ->with(
            '',
            $this->callback(function (array $options): bool {
                expect($options)->toHaveKey(RequestOptions::BODY);
                expect($options)->toHaveKey(RequestOptions::HEADERS);

                $body = $options[RequestOptions::BODY];

                expect($body)->toContain('<login>380501111111</login>');
                expect($body)->toContain('<password>secret&amp;</password>');
                expect($body)->toContain('<from>Sender</from>');
                expect($body)->toContain('<text>Hello &amp; welcome</text>');
                expect($body)->toContain('<recipient>123456789</recipient>');
                expect($options[RequestOptions::HEADERS]['Content-Type'] ?? '')->toBe('application/xml; charset=UTF-8');

                return true;
            })
        )
        ->willReturn($response);

    $client = new LetsAdsClient('380501111111', 'secret&', 'Sender', $httpClient);

    $sendResponse = $client->sendSms([
        'text' => 'Hello & welcome',
        'phone' => '+12 34-567-89',
    ]);

    expect($sendResponse)->toBeInstanceOf(SendSmsResponse::class);
    expect($sendResponse->name)->toBe('Complete');
    expect($sendResponse->description)->toBe('2 messages put into queue');
    expect($sendResponse->smsIds)->toBe(['633217', '633218']);
});

it('uses per-message from when provided', function () {
    $httpClient = $this->createMock(HttpClient::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')
        ->willReturn('<?xml version="1.0" encoding="UTF-8"?><response><name>Complete</name><description>1 messages put into queue</description><sms_id>633217</sms_id></response>');

    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('post')
        ->with(
            '',
            $this->callback(function (array $options): bool {
                $body = $options[RequestOptions::BODY];
                expect($body)->toContain('<from>CustomFrom</from>');

                return true;
            })
        )
        ->willReturn($response);

    $client = new LetsAdsClient('380501111111', 'secret', 'DefaultFrom', $httpClient);

    $client->sendSms([
        'text' => 'Hi',
        'phone' => '380991112233',
        'from' => 'CustomFrom',
    ]);
});

it('posts get balance request with expected XML body', function () {
    $httpClient = $this->createMock(HttpClient::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')
        ->willReturn('<?xml version="1.0" encoding="UTF-8"?><response><name>Balance</name><description>Кількість коштів на рахунку користувача</description><currency>Валюта рахунку користувача</currency></response>');

    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('post')
        ->with(
            '',
            $this->callback(function (array $options): bool {
                expect($options)->toHaveKey(RequestOptions::BODY);
                expect($options)->toHaveKey(RequestOptions::HEADERS);

                $body = $options[RequestOptions::BODY];

                expect($body)->toContain('<login>380501111111</login>');
                expect($body)->toContain('<password>secret&amp;</password>');
                expect($body)->toContain('<balance />');
                expect($options[RequestOptions::HEADERS]['Content-Type'] ?? '')->toBe('application/xml; charset=UTF-8');

                return true;
            })
        )
        ->willReturn($response);

    $client = new LetsAdsClient('380501111111', 'secret&', 'Sender', $httpClient);

    $balance = $client->getBalance();

    expect($balance)->toBeInstanceOf(BalanceResponse::class);
    expect($balance->name)->toBe('Balance');
    expect($balance->description)->toBe('Кількість коштів на рахунку користувача');
    expect($balance->currency)->toBe('Валюта рахунку користувача');
});

it('throws detailed exception when send SMS response is an error', function () {
    $httpClient = $this->createMock(HttpClient::class);
    $stream = $this->createMock(StreamInterface::class);
    $response = $this->createMock(ResponseInterface::class);

    $stream->method('__toString')
        ->willReturn('<?xml version="1.0" encoding="UTF-8"?><response><name>Error</name><description>AUTH_DATA</description></response>');

    $response->method('getBody')->willReturn($stream);

    $httpClient->expects($this->once())
        ->method('post')
        ->willReturn($response);

    $client = new LetsAdsClient('380501111111', 'secret&', 'Sender', $httpClient);

    $client->sendSms([
        'text' => 'Hello',
        'phone' => '+380991112233',
    ]);
})->throws(LetsAdsApiException::class, 'LetsAds API error: Error - AUTH_DATA');
