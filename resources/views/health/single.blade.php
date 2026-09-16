<x-layouts.master>
    <article class="health-journal health-single health-topic-{{ $entry['topic'] ?? 'weight' }}">
        <a class="health-back" href="{{ get_post_type_archive_link('health_entry') }}">← Health journal</a>
        @if(is_array($entry) && isset($entry['providers']))
        <header class="health-intro"><div><p class="health-eyebrow">{{ $entry['date'] }} / {{ $entry['timezone'] }}</p><h1>{{ \App\Health\MetricCatalog::TOPICS[$entry['topic']] }}</h1></div><p>One day, in detail.<br><span>Each provider keeps its own measurements. Values are never added together across providers.</span></p></header>
        @foreach($entry['providers'] as $source => $section)
            <section class="health-provider">
                <div class="health-provider-heading"><h2>{{ ucfirst($source) }}</h2><p>Fetched <time datetime="{{ $section['fetched_at'] }}">{{ $section['fetched_at'] }}</time></p></div>
                @if(count($section['metrics']))
                <div class="health-table-wrap"><table><caption>{{ ucfirst($source) }} measurements</caption><thead><tr><th scope="col">Measurement</th><th scope="col">Value</th><th scope="col">Recorded at</th></tr></thead><tbody>
                    @foreach($section['metrics'] as $metric)<tr><th scope="row">{{ $metric['label'] }}</th><td>{{ \App\Health\Presentation::number($metric['value']) }} <span>{{ $metric['unit'] }}</span></td><td><time datetime="{{ $metric['at'] }}">{{ (new DateTimeImmutable($metric['at']))->setTimezone(new DateTimeZone($entry['timezone']))->format('d M H:i') }}</time></td></tr>@endforeach
                </tbody></table></div>
                @endif
                @foreach($section['series'] as $series)
                    @php $chart = \App\Health\Presentation::chart($series['points']); $chartId = 'health-chart-'.$loop->parent->index.'-'.$loop->index; @endphp
                    <figure class="health-chart"><figcaption id="{{ $chartId }}">{{ $series['label'] }} <span>{{ $series['unit'] }}</span></figcaption>
                        <svg viewBox="0 0 640 170" role="img" aria-labelledby="{{ $chartId }}" preserveAspectRatio="none"><title>{{ $series['label'] }}: {{ \App\Health\Presentation::number($chart['min']) }} to {{ \App\Health\Presentation::number($chart['max']) }} {{ $series['unit'] }}</title><path d="M12 22H628 M12 85H628 M12 148H628" class="health-chart-grid"/><polyline points="{{ $chart['path'] }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/></svg>
                        <div class="health-chart-range"><span>Min {{ \App\Health\Presentation::number($chart['min']) }} {{ $series['unit'] }}</span><span>Max {{ \App\Health\Presentation::number($chart['max']) }} {{ $series['unit'] }}</span></div>
                        <details><summary>View {{ count($series['points']) }} recorded values</summary><div class="health-table-wrap health-series-table"><table><caption>{{ $series['label'] }} recorded values in {{ $entry['timezone'] }}</caption><thead><tr><th scope="col">Time</th><th scope="col">{{ $series['unit'] }}</th></tr></thead><tbody>@foreach($series['points'] as $point)<tr><th scope="row"><time datetime="{{ $point['at'] }}">{{ (new DateTimeImmutable($point['at']))->setTimezone(new DateTimeZone($entry['timezone']))->format('d M H:i:s') }}</time></th><td>{{ \App\Health\Presentation::number($point['value']) }}</td></tr>@endforeach</tbody></table></div></details>
                    </figure>
                @endforeach
            </section>
        @endforeach
        <p class="health-footnote">Personal measurements, not medical advice. <a href="{{ rest_url('wp/v2/health-entries/'.$post->ID) }}">Read this entry as structured data ↗</a></p>
        @else<h1>{{ $post->post_title }}</h1><p>This entry has no published measurements.</p>@endif
    </article>
</x-layouts.master>
