@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
   @php
       $lang = ICL_LANGUAGE_CODE;
   @endphp
   @includeWhen($lang == 'ro', 'generic.home_ro')
   @includeWhen($lang != 'ro', 'generic.home_en')
@overwrite
