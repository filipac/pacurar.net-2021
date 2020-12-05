@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
    <div class="m-8">
        <div class="grid grid-cols-3 gap-4">
            @for($x = 1; $x<=10; $x++)
                <div class="bg-white p-6">
                    <h2>Test postare</h2>
                </div>
            @endfor
        </div>
    </div>
@overwrite
