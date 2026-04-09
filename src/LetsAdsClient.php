<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * @see https://letsads.com/api-sms-povidomlennia
 */
class LetsAdsClient
{
    private const string BASE_URI = 'https://api.letsads.com';

    private Client $httpClient;

    public function __construct(
        private readonly string $login,
        private readonly string $password,
        private readonly string $from,
        ?Client $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => self::BASE_URI,
        ]);
    }

    /**
     * @param  array{text: string, phone: string, from?: string|null}  $parameters
     */
    public function sendSms(array $parameters): SendSmsResponse
    {
        $text = $parameters['text'];
        $phone = self::normalizePhone($parameters['phone']);
        $from = $parameters['from'] ?? $this->from;

        $xml = $this->buildSendXml($from, $text, $phone);

        $response = $this->httpClient->post('', [
            RequestOptions::BODY => $xml,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ],
        ]);

        return $this->parseSendSmsResponse((string) $response->getBody());
    }

    public function getBalance(): BalanceResponse
    {
        $xml = $this->buildBalanceXml();

        $response = $this->httpClient->post('', [
            RequestOptions::BODY => $xml,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ],
        ]);

        return $this->parseBalanceResponse((string) $response->getBody());
    }

    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function buildSendXml(string $from, string $text, string $recipient): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<request>'
            .'<auth>'
            .'<login>'.$this->escape($this->login).'</login>'
            .'<password>'.$this->escape($this->password).'</password>'
            .'</auth>'
            .'<message>'
            .'<from>'.$this->escape($from).'</from>'
            .'<text>'.$this->escape($text).'</text>'
            .'<recipient>'.$this->escape($recipient).'</recipient>'
            .'</message>'
            .'</request>';
    }

    private function buildBalanceXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<request>'
            .'<auth>'
            .'<login>'.$this->escape($this->login).'</login>'
            .'<password>'.$this->escape($this->password).'</password>'
            .'</auth>'
            .'<balance />'
            .'</request>';
    }

    private function parseBalanceResponse(string $xml): BalanceResponse
    {
        $response = simplexml_load_string($xml);

        if ($response === false) {
            throw new \RuntimeException('Could not parse LetsAds balance response XML.');
        }

        return new BalanceResponse(
            name: (string) ($response->name ?? ''),
            description: (string) ($response->description ?? ''),
            currency: (string) ($response->currency ?? ''),
        );
    }

    private function parseSendSmsResponse(string $xml): SendSmsResponse
    {
        $response = simplexml_load_string($xml);

        if ($response === false) {
            throw new \RuntimeException('Could not parse LetsAds send SMS response XML.');
        }

        $name = (string) ($response->name ?? '');
        $description = (string) ($response->description ?? '');

        if (strcasecmp($name, 'Error') === 0) {
            throw LetsAdsApiException::fromResponse($name, $description);
        }

        $smsIds = [];

        foreach ($response->sms_id ?? [] as $smsId) {
            $smsIds[] = (string) $smsId;
        }

        return new SendSmsResponse(
            name: $name,
            description: $description,
            smsIds: $smsIds,
        );
    }
}
