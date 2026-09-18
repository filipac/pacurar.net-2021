
@foreach($posts as $post)
    @dump($post->getAcfFields(), $post)
@endforeach
