<?php

use Andriichuk\LetsAdsSmsChannel\LetsAdsClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

it('posts send SMS request with expected XML body', function () {
    $httpClient = $this->createMock(HttpClient::class);

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
        ->willReturn($this->createMock(ResponseInterface::class));

    $client = new LetsAdsClient('380501111111', 'secret&', 'Sender', $httpClient);

    $client->sendSms([
        'text' => 'Hello & welcome',
        'phone' => '+12 34-567-89',
    ]);
});

it('uses per-message from when provided', function () {
    $httpClient = $this->createMock(HttpClient::class);

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
        ->willReturn($this->createMock(ResponseInterface::class));

    $client = new LetsAdsClient('380501111111', 'secret', 'DefaultFrom', $httpClient);

    $client->sendSms([
        'text' => 'Hi',
        'phone' => '380991112233',
        'from' => 'CustomFrom',
    ]);
});
