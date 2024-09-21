<?php

namespace App\Console\Commands;

use App\Services\CryptoPrice;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class CryptoPriceAnalyze extends Command
{
    protected $signature = 'crypto:analyze {pair}';
    protected $description = 'Get min and max price for a given currency pair';

    protected $client;
    protected $cryptoPrice;

    public function __construct(CryptoPrice $cryptoPrice)
    {
        parent::__construct();

        $this->client = new Client();
        $this->cryptoPrice = $cryptoPrice;
    }

    public function handle()
    {
        $pair = strtoupper($this->argument('pair'));

        $this->info("Get prices for {$pair}...");

        [$prices, $getStatus] = $this->cryptoPrice->fetchPrices($pair);

        $falseKeys = [];
        foreach ($getStatus as $key => $value) {
            if (!$value) {
                $falseKeys[] = $key;
            }
        }

        if (!empty($prices) && empty($falseKeys)) {
            $minExchange = array_search(min($prices), $prices);
            $maxExchange = array_search(max($prices), $prices);
            $min = min($prices);
            $max = max($prices);

            $this->info("Min price: {$min} on {$minExchange}, Max price: {$max} on {$maxExchange}");
        } else {
            $this->error("No prices found for the pair: {$pair}");
        }
    }
}
