<?php

declare(strict_types=1);

namespace Andriichuk\LetsAdsSmsChannel;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

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
    public function sendSms(array $parameters): ResponseInterface
    {
        $text = $parameters['text'];
        $phone = self::normalizePhone($parameters['phone']);
        $from = $parameters['from'] ?? $this->from;

        $xml = $this->buildSendXml($from, $text, $phone);

        return $this->httpClient->post('', [
            RequestOptions::BODY => $xml,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ],
        ]);
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
}
