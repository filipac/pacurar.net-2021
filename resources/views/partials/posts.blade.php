<div class="flex flex-col divide-y" style="border-color: var(--color-outline-variant);">
@foreach($posts as $_post)
@php
    global $post;
    if($_post instanceof App\Models\Wp\Post\Post) {
        $post = $_post->wpPost();
        setup_postdata($_post->wpPost());
    }
@endphp
<div class="py-6 first:pt-0">
    @includeWhen(class_basename($_post) == 'stdClass' && $_post->ID == 'rand', 'partials.home.post_random_box', ['_post' => $_post])
    @includeWhen(class_basename($_post) == 'Post', 'partials.home.post_box', ['_post' => $_post])
</div>
@endforeach
</div>
