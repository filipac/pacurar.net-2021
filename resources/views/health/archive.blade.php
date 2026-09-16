<x-layouts.master>
    <div class="health-journal">
        <header class="health-intro">
            <div><p class="health-eyebrow">THE OPEN NOTEBOOK / HEALTH</p><h1>{{ config('health.title') }}</h1></div>
            <p>{{ config('health.description') }}<br><span>Each entry shows its measurement date. Today’s data is preferred; missing sources may use yesterday’s data.</span></p>
        </header>
        <form class="health-filters" method="get" action="{{ get_post_type_archive_link('health_entry') }}">
            <label>Topic<select name="topic"><option value="">All topics</option>@foreach(\App\Health\MetricCatalog::TOPICS as $slug => $label)<option value="{{ $slug }}" @selected(request('topic') === $slug)>{{ $label }}</option>@endforeach</select></label>
            <label>Source<select name="source"><option value="">Both providers</option><option value="withings" @selected(request('source') === 'withings')>Withings</option><option value="oura" @selected(request('source') === 'oura')>Oura</option></select></label>
            <button type="submit">Filter entries <span aria-hidden="true">↗</span></button>
            <span class="health-count">{{ $query->found_posts }} {{ $query->found_posts === 1 ? 'entry' : 'entries' }}</span>
        </form>
        @if(count($posts))
            <div class="health-grid" x-data="healthMasonry">
                @foreach($posts as $healthPost)
                    @php
                        $entry = get_post_meta($healthPost->ID, '_health_data', true);
                        $metric = is_array($entry) ? \App\Health\Presentation::prominent($entry) : null;
                    @endphp
                    @if(is_array($entry) && isset($entry['topic']))
                    <article class="health-card health-topic-{{ $entry['topic'] }}">
                        <div class="health-card-heading"><span class="health-topic-label">{{ \App\Health\MetricCatalog::TOPICS[$entry['topic']] }}</span><time datetime="{{ $entry['date'] }}">{{ date('d M Y', strtotime($entry['date'])) }}</time></div>
                        <h2><a href="{{ get_permalink($healthPost) }}">@if($metric)<span class="health-big-number">{{ \App\Health\Presentation::number($metric['value']) }}</span> <span class="health-unit">{{ $metric['unit'] }}</span>@else<span class="health-series-title">A day in detail</span>@endif</a></h2>
                        @if($metric)<p class="health-metric-label">{{ $metric['label'] }} <span>· {{ ucfirst($metric['source']) }}</span></p>@endif
                        <dl class="health-card-details">
                        @foreach($entry['providers'] as $source => $section)
                            @foreach(array_slice($section['metrics'], 0, 3) as $detail)
                                @if(!$metric || $detail['key'] !== $metric['key'])<div><dt>{{ $detail['label'] }} <small>{{ ucfirst($source) }}</small></dt><dd>{{ \App\Health\Presentation::number($detail['value']) }} <small>{{ $detail['unit'] }}</small></dd></div>@endif
                            @endforeach
                            @if(count($section['series']))<div><dt>{{ ucfirst($source) }} time series</dt><dd>{{ count($section['series']) }}</dd></div>@endif
                        @endforeach
                        </dl>
                        <footer><div class="health-badges">@foreach(array_keys($entry['providers']) as $source)<span>{{ ucfirst($source) }}</span>@endforeach</div><a href="{{ get_permalink($healthPost) }}" aria-label="View {{ \App\Health\MetricCatalog::TOPICS[$entry['topic']] }} for {{ $entry['date'] }}">Details ↗</a></footer>
                    </article>
                    @endif
                @endforeach
            </div>
            <nav class="health-pagination" aria-label="Health journal pages">{!! paginate_links(['total' => $query->max_num_pages, 'current' => max(1, get_query_var('paged')), 'type' => 'list']) !!}</nav>
        @else
            <div class="health-empty"><p class="health-eyebrow">THE FIRST PAGE IS STILL OPEN</p><h2>No entries here yet.</h2><p>Published measurements will appear here as the journal grows.</p></div>
        @endif
        <p class="health-footnote">Personal measurements, not medical advice. Missing data stays missing. Provider values are shown separately.<br>Structured measurements are also available through the <a href="{{ rest_url('wp/v2/health-entries') }}">public health data API</a>.</p>
    </div>
</x-layouts.master>
