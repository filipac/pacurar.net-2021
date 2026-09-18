<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ExchangeEgold extends Component
{
    public $amount = 1;

    protected $queryString = ['amount'];

    public function render()
    {
        return view('livewire.exchange');
    }

    public function price(): string
    {
        // This public ticker is the same last-trade price previously read through CCXT.
        $ticker = Http::acceptJson()->connectTimeout(5)->timeout(10)
            ->get('https://api.binance.com/api/v3/ticker/24hr', ['symbol' => 'EGLDEUR'])
            ->throw()->json();
        $price = $ticker['lastPrice'] ?? null;
        if (($ticker['symbol'] ?? null) !== 'EGLDEUR' || ! is_string($price)
            || ! preg_match('/^\d+(?:\.\d+)?$/D', $price) || bccomp($price, '0', 10) <= 0) {
            throw new \RuntimeException('Binance did not return a valid EGLD/EUR price.');
        }

        return bcdiv('1', $price, 10);
    }
}
