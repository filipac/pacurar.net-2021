<x-layouts.master>
    <article class="health-journal health-single health-topic-{{ $entry['topic'] ?? 'weight' }}">
        <a class="health-back" href="{{ get_post_type_archive_link('health_entry') }}">← Health journal</a>
        @if(is_array($entry) && isset($entry['providers']))
        <header class="health-intro"><div><p class="health-eyebrow">{{ $entry['date'] }} / {{ $entry['timezone'] }}</p><h1>{{ \App\Health\MetricCatalog::TOPICS[$entry['topic']] }}</h1></div><p>One day, in detail.<br><span>Each provider keeps its own measurements. Values are never added together across providers.</span></p></header>
        @php $metric = \App\Health\Presentation::prominent($entry); @endphp
        @if($metric)
        <section class="health-featured-metric" aria-label="Featured measurement">
            <p class="health-featured-value"><span class="health-big-number">{{ \App\Health\Presentation::number($metric['value']) }}</span> <span class="health-unit">{{ $metric['unit'] }}</span></p>
            <p class="health-metric-label">{{ $metric['label'] }} <span>· {{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }}</span></p>
            @include('health.comparison', ['comparison' => $comparisons->metric($entry['topic'], $entry['date'], $metric['key'].'|daily'), 'compact' => true])
        </section>
        @endif
        @foreach($entry['providers'] as $source => $section)
            <section class="health-provider">
                <div class="health-provider-heading"><h2>{{ \App\Health\MetricCatalog::SOURCES[$source] }}</h2><p>Fetched <time datetime="{{ $section['fetched_at'] }}">{{ $section['fetched_at'] }}</time></p></div>
                @include('health.measurements', ['chartPrefix' => 'health-chart-'.$source, 'historyContext' => 'daily'])
                @foreach($section['workouts'] ?? [] as $workout)
                    <section class="health-workout">
                        <h3>{{ \App\Health\MetricCatalog::WORKOUT_TYPES[$workout['type']] }}</h3>
                        <p><time datetime="{{ $workout['start'] }}">{{ (new DateTimeImmutable($workout['start']))->setTimezone(new DateTimeZone($entry['timezone']))->format('d M H:i') }}</time> – <time datetime="{{ $workout['end'] }}">{{ (new DateTimeImmutable($workout['end']))->setTimezone(new DateTimeZone($entry['timezone']))->format('H:i') }}</time> · {{ \App\Health\MetricCatalog::SOURCES[$workout['origin']] }}</p>
                        @include('health.measurements', ['section' => $workout, 'chartPrefix' => 'health-workout-'.$source.'-'.$loop->index, 'historyContext' => $workout['type'].'|'.$workout['origin']])
                    </section>
                @endforeach
            </section>
        @endforeach
        <p class="health-footnote">Changes use the same provider and metric on the exact earlier date. Weekly trends cover the seven days ending on this entry's date; missing days remain gaps. Repeated readings and sample series use daily averages; workout comparisons keep activity type and origin separate. Timestamp changes compare local clock times; pp means percentage points. Increases and decreases do not imply better or worse health.<br>Personal measurements, not medical advice. <a href="{{ rest_url('wp/v2/health-entries/'.$post->ID) }}">Read this entry as structured data ↗</a></p>
        @else<h1>{{ $post->post_title }}</h1><p>This entry has no published measurements.</p>@endif
    </article>
</x-layouts.master>
