<html>
<head>
    <style>{!! file_get_contents(public_path('css/app.css')) !!}</style>
    <link rel="stylesheet" href="https://use.typekit.net/rqd7nkn.css">
    <link href="https://fonts.googleapis.com/css?family=Sigmar+One&display=swap" rel="stylesheet">
</head>
<body>
<div class="min-h-screen bg-splash py-6 flex flex-col justify-center sm:py-12 p-20">
    <div
        class="absolute shadow-box inset-0 bg-gradient-to-r {{ $post->gradient_colors }} shadow-lg"
        @if($featured_image = get_the_post_thumbnail_url($post->id, 'full'))
            style="
            background-image: url('{{ $featured_image }}') !important;
            background-size: cover;
            "
        @endif
    ></div>
    <div class="relative py-3">
        <div class="relative px-4 py-10 bg-white shadow-xl sm:p-10 shadow-boxhvr">
            <div class="mx-auto">
                <div class="divide-y divide-gray-200">
                    <div class="pb-4 text-base space-y-4 leading-9">
                        <p class="text-3xl font-bold">{!! $post->title !!}</p>
                    </div>
                    @php
                        $streak = get_option('current_daily_streak_100d');
                    @endphp
                    @if($streak > 0)
                        <div class="p-2 bg-yellow text-sm mb-2">
                            @if(ICL_LANGUAGE_CODE == 'en')
                                I'm currently on a #writeDaily streak. Posted daily
                                for {{ $streak }} {{ $streak == 0 ? '😢' : '' }} {{ $streak != 0 && $streak < 5 ? '😄' : '' }} {{ $streak != 0 && $streak > 5 ? '💪' : '' }}
                                consecutive days.
                            @else
                                Sunt intr-un streak la challenge-ul #writeDaily. Am postat
                                pentru {{$streak}} {{ $streak == 0 ? '😢' : '' }} {{ $streak != 0 && $streak < 5 ? '😄' : '' }} {{ $streak != 0 && $streak > 5 ? '💪' : '' }}
                                zile consecutive.
                            @endif
                        </div>
                    @endif
                    <div class="pt-6 text-base leading-6 font-bold sm:text-lg sm:leading-7">
                        <div class=" flex flex-row-reverse justify-between items-center">
                            <figure class="inline-block mb-1 md:mb-0 md:mr-3">
                                <a href="/" title="">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                         viewBox="0 0 1355 493" style="max-width: 250px; min-width: 200px;">
                                        <defs />
                                        <image width="442" height="442" x="21" y="18"
                                               xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAboAAAG6CAMAAABJKXO3AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACnVBMVEUALzz///8ALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwAAAD8JAJdAAAA3XRSTlMAAAkOEBEtebbR3uLo7O7t6+fj1LNwKQ0HDzZQWl50svn74atxXU0GARdLl8vl9fTk3cSHOggFAi9hgI6w/teliXtVIhtDg8r9uTUYP7za+vI7HFuWkRIaa7jY6apTDDl2wvOvZzQUSvjTRShoqdtiHyx4w/G/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" />
                                        <text x="237.843" y="384.75" fill="#fff" font-family="Bariol" font-size="430"
                                              text-anchor="middle">
                                            <tspan x="237.843">f</tspan>
                                        </text>
                                        <text x="908.885" y="290.25" fill="#002f3c" data-name="ilip pacurar"
                                              font-family="Bariol Regular" font-size="200" text-anchor="middle">
                                            <tspan x="908.885">ilip pacurar</tspan>
                                        </text>
                                    </svg>
                                </a>
                            </figure>
                            <div>
                                <h1 class="text-lg uppercase tracking-wider font-extrabold">
                                    <a href="/">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Pacurar.net' : 'Pacurar.dev' }}</a>
                                </h1>
                                <p class="text-sm font-bold text-gray-600">
                                <div class="inline-flex flex-wrap flex-gap text-xs mt-4 parentapp"
                                >
                                    @php
                                        $p = $post->wpPost();
                                        $tags = get_the_tags($p) ?: [];
                                        $categories = get_the_terms( $p, 'category' ) ?: [];
                                    @endphp

                                    @foreach($categories as $cat)
                                        <a class="bg-green-200 text-black p-2 shadow-box hover:shadow-boxhvr block prevent-if"
                                           href="{{ get_term_link($cat) }}">{!! $cat->name !!}</a>
                                    @endforeach
                                    @if(count($tags) > 0)
                                        <a class="bg-yellow text-black p-2 shadow-box hover:shadow-boxhvr block prevent-if"
                                           href="{{ get_term_link($tags[0]) }}">#{!! $tags[0]->name !!}</a>
                                    @endif
                                    @foreach($tags as $tag)
                                        @if($loop->index == 0)
                                            @continue
                                        @endif
                                        <a class="bg-yellow text-black p-2 shadow-box hover:shadow-boxhvr transition-opacity prevent-if"
                                           href="{{ get_term_link($tag) }}">#{!! $tag->name !!}</a>
                                    @endforeach
                                </div>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
