<?php

/** Exchange compatibility checks with fake HTTP; no Binance calls or credentials. */
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Livewire\ExchangeEgold;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

$checks = 0;
$fake = function (mixed $response, int $status = 200): Factory {
    $http = new Factory;
    $http->preventStrayRequests();
    $http->fake(['api.binance.com/api/v3/ticker/24hr*' => Factory::response($response, $status)]);
    Http::swap($http);

    return $http;
};
$check = function (bool $condition, string $label) use (&$checks): void {
    if (! $condition) {
        throw new RuntimeException($label);
    }
    $checks++;
};

$http = $fake(['symbol' => 'EGLDEUR', 'lastPrice' => '25.00000000']);
$widget = new ExchangeEgold;
$check($widget->price() === '0.0400000000', 'Same EUR to EGLD conversion');
$request = $http->recorded()[0][0];
$check($request->method() === 'GET' && $request->url() === 'https://api.binance.com/api/v3/ticker/24hr?symbol=EGLDEUR', 'Only the requested public ticker is fetched');
$check(! $request->hasHeader('X-MBX-APIKEY') && ! $request->hasHeader('Authorization'), 'Public lookup sends no account credentials');
foreach ([null, '0', '-1', 'NaN', [], '1e2'] as $invalid) {
    $fake(['symbol' => 'EGLDEUR', 'lastPrice' => $invalid]);
    $rejected = false;
    try {
        $widget->price();
    } catch (RuntimeException $error) {
        $rejected = true;
    }
    $check($rejected, 'Invalid price is never displayed or divided by');
}
$fake(['symbol' => 'BTCUSDT', 'lastPrice' => '25']);
$rejected = false;
try {
    $widget->price();
} catch (RuntimeException $error) {
    $rejected = true;
}
$check($rejected, 'Wrong pair rejected');
$fake(['code' => -1003], 429);
$rejected = false;
try {
    $widget->price();
} catch (RequestException $error) {
    $rejected = true;
}
$check($rejected, 'Provider failure propagates without a fabricated price');
echo "$checks exchange compatibility checks passed\n";
