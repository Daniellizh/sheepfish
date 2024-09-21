<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CryptoPrice
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function fetchPrices($pair)
    {
        $underscore = str_replace('/', '_', $pair);
        $withoutSlash = str_replace('/', '', $pair);

        $exchanges = [
            'binance' => 'https://api.binance.com/api/v3/ticker/price?symbol=' . $withoutSlash,
            'jbex' => 'https://api.jbex.com/openapi/quote/v1/ticker/price?symbol=' . $withoutSlash,
            'poloniex' => "https://api.poloniex.com/markets/{$underscore}/markPrice",
            'bybit' => 'https://api-testnet.bybit.com/v5/market/tickers?category=inverse&symbol=' . $withoutSlash,
            'whitebit' => 'https://whitebit.com/api/v4/public/ticker',
        ];

        $prices = [];
        $getStatus = [];

        foreach ($exchanges as $exchange => $url) {
            try {
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'accept' => 'application/json',
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $getStatus[$exchange] = true;
                    $body = json_decode($response->getBody(), true);

                    $prices[$exchange] = $this->extractPrice($exchange, $body, $underscore);
                } else {
                    $getStatus[$exchange] = false;
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $getStatus[$exchange] = false;
            }
        }

        return [$prices, $getStatus];
    }

    private function extractPrice($exchange, $body, $underscore)
    {
        switch ($exchange) {
            case 'binance':
                return $body['price'];
            case 'jbex':
                return $body['price'];
            case 'poloniex':
                return $body['markPrice'];
            case 'bybit':
                if(!empty($body['result']['list'])){
                    return $body['result']['list'][0]['indexPrice'];
                }
                break;
            case 'whitebit':
                if(isset($body[$underscore])){
                    return $body[$underscore]['last_price'];
                }
                break;
            default:
                return null;
        }
    }
}
