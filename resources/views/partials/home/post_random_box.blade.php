@php
    $random = collect([1,2,3,3,4,4,4])->random();
    //$random = 4;
@endphp
@include('partials.home.random_'.$random)
