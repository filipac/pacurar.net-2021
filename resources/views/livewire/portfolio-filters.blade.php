<div class="flex flex-wrap gap-2">
    @foreach($techs as $idx => $tech)
    <div class="cursor-pointer px-3 py-2 font-label text-xs uppercase tracking-wider transition-colors"
        style="{{ count($narrowTo) == 0 ? 'background: var(--color-surface-container);' : (in_array($tech->slug, $narrowTo) ? 'background: var(--color-primary); color: #fff;' : 'background: var(--color-surface-container-low); color: var(--color-on-surface-variant);') }} border-radius: 0.125rem;"
        wire:click="toggle('{{ $tech->slug }}')">
        {{ $tech->name }}
    </div>
    @endforeach
</div>
