<div>
    <div class="widget-title">{{ $attributes->get('title') }}</div>
    <div {{$attributes->merge(['class' => 'widget-inner'])}}>{{ $slot }}</div>
</div>
