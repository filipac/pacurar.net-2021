<div class="inline-flex flex-wrap gap-2 text-xs mt-3">
    @foreach($categories as $cat)
    <button class="font-label uppercase tracking-wider px-2 py-1 transition-colors cursor-pointer"
        style="background: var(--color-surface-container); color: var(--color-on-surface); border-radius: 0.125rem; font-size: 0.625rem;"
        wire:click.prevent="$dispatch('toggle', {id: '{{ $cat->slug }}')"
    >{{ $cat->name }}</button>
    @endforeach
</div>
