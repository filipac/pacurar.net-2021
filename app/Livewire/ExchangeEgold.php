<?php

namespace App\Livewire;

use Illuminate\Support\Arr;
use Livewire\Component;

class ExchangeEgold extends Component
{
    public $amount = 1;

    protected $queryString = ['amount'];

    public function render()
    {
        return view('livewire.exchange');
    }

    public function price()
    {
        $x = new \ccxt\binance(['apiKey' => env('BINANCE_KEY'), 'secret' => env('BINANCE_SECRET')]);
        $x = $x->fetch_ticker('EGLD/EUR');
//        dd($x);
        $ask = Arr::get($x, 'last', 0);
//        $percentage = bcmul(1.05, $ask, 10);
        $unEgld = bcadd($ask, 0, 10);

        return bcdiv(1, $unEgld, 10);

        /*
         * $unEgld .. 1
         * 1 ..... x
         */

//        $unEgld ........ 1;
//        10 ............. x
//        ray(bcadd($ask, $percentage, 10), $ask);
        return $unEgld;
    }
}
