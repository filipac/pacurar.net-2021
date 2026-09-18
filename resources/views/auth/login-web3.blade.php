@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@once
    @push('scripts')
        <script src="/tinymce/tinymce.min.js"></script>
        @viteReactRefresh
        @vite(['resources/js/react-app.tsx'])
    @endpush
@endonce

<x-layouts.master extra-classes-content=" min-h-header-home ">
    <x-slot name="belowContent">
        <x-content-with-sidebar>
            <div class="px-2 lg:px-0">
                <div data-web3-state-management data-login="1"
                     @if($intended = Session::get('url.intended')) data-intended="{{$intended}}" @endif
                ></div>
            </div>
        </x-content-with-sidebar>
    </x-slot>
</x-layouts.master>
