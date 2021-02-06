<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 relative">
    <template x-if="gem">
<div class="absolute w-full flex h-screen flex flex-col items-center">
    <div class="w-full mt-16">
        <div class="w-full text-center">
            @if(ICL_LANGUAGE_CODE == 'ro')
            <h1 class="text-white text-4xl">Cam cat de tocilar esti?</h1>
            <div class="text-white text-center w-full">
                <p>Nu ma gandeam ca prea multa lume din cercul meu stie de konami code.</p>
                <p>De obicei apare melodia de rickroll cand introduci codul, dar era un cliseu prea mare nu?</p>
                <p>Eu l-am invitat pe John Cena sa ne incante!!!!!!!!!</p>
            </div>
            @else
            <h1 class="text-white text-4xl">You are a nerd, you know that?</h1>
            <div class="text-white text-center w-full">
                <p>Almost nobody from my close circle knows about the konami code.</p>
                <p>Usually the rickroll music plays when you enter the konami code, but I like to avoid cliches.</p>
                <p>I invited John Cena to sing for us!!!!!!!!!</p>
            </div>
            @endif
        </div>
        <div class='widescreen responsive-embed mt-16'>
        <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/wo-j4-ES6GE?controls=0&amp;start=24&autoplay=1&disablekb=1&fs=0&loop=1&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>
    </template>
@php
    $rowsFilled = [0];
    $rowIndex = 0;
    $now = null;
    $lastRand = null;
@endphp
@foreach($posts as $_post)
@php
    global $post;
    if($_post instanceof App\Models\Wp\Post\Post) {
        $post  = $_post->wpPost();
        setup_postdata($_post->wpPost());
    }
    if(!$now) {
        $now = rand(1,3);
    } else {
        $now = 3 - rand(1, $now);
    }
    $rand = $now;
    $rowsFilled[$rowIndex] += $rand;
    if($rowsFilled[$rowIndex] > 3) {
     $rand = 3 - $lastRand;
     $rowsFilled[$rowIndex] = 3;
    }

    if($rowsFilled[$rowIndex] == 3 && !$loop->last) {
        $rowIndex++;
        $rowsFilled[$rowIndex] = 0;
    }

    if($loop->last && $rowsFilled[$rowIndex] != 3) {
        $leftToFill = 3 - $rowsFilled[$rowIndex];
        $rand += $leftToFill;
    }

    if($now == 3) {
        $now = null;
    }

    $lastRand = $rand;
    // if(!isset($prevRand)) {
    //     $prevRand = 0;
    // }
    // if($prevRand == 3) {
    //     $rand = rand(1,3);
    // } else if($prevRand == 2) {
    //     $rand = 1;
    // } else if($prevRand == 1) {
    //     $rand = rand(1,2);
    // } else {
    //     $rand = rand(1,3);
    // }
@endphp
<div class="post-box bg-white col-span-1 md:col-span-{{$rand}} shadow-box">
    @includeWhen(class_basename($_post) == 'stdClass' && $_post->ID == 'rand', 'partials.home.post_random_box', ['_post' => $_post])
    @includeWhen(class_basename($_post) == 'Post', 'partials.home.post_box', ['_post' => $_post])
</div>
{{-- @php
    $prevRand = $rand;
@endphp --}}
@endforeach
</div>
