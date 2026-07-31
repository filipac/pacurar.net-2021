@php
    $random = collect([1,2,3,3])->random();
    if(request()->has('randombox')) {
        $random = request()->get('randombox');
    }
    //$random = 4;
@endphp
@include('partials.home.random_'.$random)
