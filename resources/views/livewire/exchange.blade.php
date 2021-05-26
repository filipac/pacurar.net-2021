@php $price = (float) $this->price(); @endphp
<div x-data="{ amount: @entangle('amount') }">
    <div>In order to send me</div>
    <div class="flex justify-center text-4xl">
        <div contenteditable x-on:blur="amount = $event.target.innerHTML" contenteditable="true">{{$amount}}</div>
        <div class="ml-2">EUR</div>
    </div>
    <div>equivalent in EGold</div>
    <div class="mt-3">
        you have to send
    </div>
    <div class="flex justify-center text-4xl">
        <div>{{round(bcmul($price, (float) $amount, 10), 6)}}</div>
        <div class="ml-2">EGold</div>
    </div>
    <div class="flex justify-center text-xs">
        <div>1 EUR = {{$price}} EGold</div>
    </div>
</div>
