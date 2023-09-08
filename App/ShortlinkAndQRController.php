<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(Config::ROOT_PATH);
$dotenv->load();
class ShortlinkAndQRController
{
    public function create_short_link($long_url)
    {
        $client = new Client();
        $response = $client->post(
            'https://api-ssl.bitly.com/v4/shorten',
            ['headers' => [
                'Authorization' => 'Bearer ' . $_ENV['BITLY_API_TOKEN'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',],
                'json' => [
                    'long_url' => $long_url
                ]
            ]
        );
        $body = $response->getBody();

        $response_body = json_decode($body);

        return $response_body->link;
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function get_qrcode($short_url)
    {
        $url = parse_url($short_url);

        $client = new Client();
        $response = $client->get(
            'https://api-ssl.bitly.com/v4/bitlinks/' . $url['host'] . $url['path'] . '/qr',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['BITLY_API_TOKEN']
                ]
            ]
        );
        $body = $response->getBody();
        $response_body = json_decode($body);
        return $response_body->qr_code;
    }

    public function get_qrcode_with_long_url($long_url)
    {
        return $this->get_qrcode($this->create_short_link($long_url));
    }
}