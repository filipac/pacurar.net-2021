<span class="font-label" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
    <span x-show="!hover">{{ get_the_date('Y . m . d'.(is_single() ? ' H:i' : '')) }}</span>
    <span x-show="hover" x-cloak>{{ get_the_date('U') }}</span>
</span>
