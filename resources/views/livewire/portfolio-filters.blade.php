<div class="emulated-flex-gap">
    {{-- @php
        $f = array_fill(1, 10, 1);
    @endphp --}}
    {{-- @foreach($f as $x) --}}
    @foreach($techs as $idx => $tech)
    <div class="cursor-pointer
        {{ count($narrowTo) == 0 ? ($idx % 2 == 0 ? 'bg-red-400' : 'bg-primary') : ('') }}
        {{ count($narrowTo) > 0 ? (in_array($tech->slug, $narrowTo) ? 'bg-red-400' : 'bg-gray-200') : ('') }}
        p-4 shadow-box hover:shadow-boxhvr inline-block"
        wire:click="toggle('{{ $tech->slug }}')">
        {{ $tech->name }}
    </div>
    @endforeach
    {{-- @endforeach --}}
</div>
`
