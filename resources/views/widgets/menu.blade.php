<x-widget :title="$title" class="no-padding">
    {!!
     wp_nav_menu([
        'menu' => $menu_slug,
        'container' => false,
        'menu_class' => 'menu',
        'echo' => true,
        'fallback_cb' => 'wp_page_menu',
        'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth' => 0,
    ])
    !!}
</x-widget>
