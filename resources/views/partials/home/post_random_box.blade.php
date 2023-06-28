@php
    $random = collect([1])->random();
    //$random = 4;
@endphp
@include('partials.home.random_'.$random)
