<div class="inline-flex flex-wrap flex-gap text-xs mt-4 parentapp">

    @foreach($categories as $cat)
    <button class="bg-secondary @if(isset($nolink)) dark:bg-gray-800 dark:text-white dark:shadow-box-white dark:hover:shadow-boxhvr-white @endif text-black p-2 shadow-box hover:shadow-boxhvr focus-none block prevent-if"
    wire:click.prevent="$dispatch('toggle', {id: '{{ $cat->slug }}')"
    >{{ $cat->name }}</button>
    @endforeach

</div>
