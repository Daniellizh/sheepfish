<?php

namespace App\Console\Commands;

use App\Services\CryptoPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CryptoProfitAnalysis extends Command
{
    protected $signature = 'crypto:profit {pair}';
    protected $description = 'Calculate profit percentage for a given currency pair';
    protected $cryptoPrice;

    public function __construct(CryptoPrice $cryptoPrice)
    {
        parent::__construct();
        $this->cryptoPrice = $cryptoPrice;
    }

    public function handle()
    {
        $pair = strtoupper($this->argument('pair'));

        $this->info("Fetching prices for {$pair}...");

        [$prices, $getStatus]  = $this->cryptoPrice->fetchPrices($pair);

        $falseKeys = [];
        foreach ($getStatus as $key => $value) {
            if (!$value) {
                $falseKeys[] = $key;
            }
        }

        if (count($prices) < 2) {
            $this->error("Not enough price data available for {$pair}.");
            return;
        }

        if (!empty($prices) && empty($falseKeys)) {
            $minPrice = min($prices);
            $maxPrice = max($prices);

            $minExchange = array_search($minPrice, $prices);
            $maxExchange = array_search($maxPrice, $prices);

            $profitPercentage = (($maxPrice - $minPrice) / $minPrice) * 100;

            $this->info("Min price: {$minPrice} on {$minExchange}");
            $this->info("Max price: {$maxPrice} on {$maxExchange}");
            $this->info("Profit percentage from buying on {$minExchange} and selling on {$maxExchange}: {$profitPercentage}%");
        }else{
            $this->error("No prices found for the pair: {$pair}");
        }
    }
}
