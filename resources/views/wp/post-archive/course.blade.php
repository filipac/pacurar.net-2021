
@foreach($posts as $post)
    @dump($post->getAcfFields())
@endforeach
