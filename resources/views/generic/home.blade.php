@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
   @php
       $lang = ICL_LANGUAGE_CODE;
   @endphp
   <div class="flex items-center justify-center switcher">
   @php
       do_action( 'wpml_add_language_selector' )
   @endphp
   </div>
   @includeWhen($lang == 'ro', 'generic.home_ro')
   @includeWhen($lang != 'ro', 'generic.home_en')

   <style>
       .switcher .wpml-ls-statics-shortcode_actions {
           background-color: #fff;
           margin-bottom: 20px;
       }
   </style>
@overwrite
