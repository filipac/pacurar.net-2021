@php
    // Pair cards between full-width notes/interludes without changing post order.
    $postRows = [];
    $unpaired = null;
    foreach ($posts as $entry) {
        $isInterlude = class_basename($entry) == 'stdClass' && $entry->ID == 'rand';
        $wide = $isInterlude || has_post_format('aside', $entry->wpPost());
        if ($wide && $unpaired !== null) {
            $postRows[$unpaired]['wide'] = true;
            $unpaired = null;
        } elseif (!$wide) {
            $unpaired = $unpaired === null ? count($postRows) : null;
        }
        $postRows[] = ['post' => $entry, 'wide' => $wide, 'interlude' => $isInterlude];
    }
    if ($unpaired !== null) {
        $postRows[$unpaired]['wide'] = true;
    }
@endphp
<div class="journal-posts grid grid-cols-1 md:grid-cols-2 gap-6">
@foreach($postRows as $row)
@php
    $_post = $row['post'];
    global $post;
    if($_post instanceof App\Models\Wp\Post\Post) {
        $post = $_post->wpPost();
        setup_postdata($post);
    }
@endphp
<div class="journal-post-row {{ $row['wide'] ? 'journal-post-wide md:col-span-2' : '' }} {{ $row['interlude'] ? 'journal-post-interlude' : '' }}">
    @includeWhen($row['interlude'], 'partials.home.post_random_box', ['_post' => $_post])
    @includeWhen(class_basename($_post) == 'Post', 'partials.home.post_box', ['_post' => $_post])
</div>
@endforeach
</div>
