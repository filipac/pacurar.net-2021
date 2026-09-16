<x-layouts.master title="Health trends · Filip Pacurar">
    <div class="health-journal health-comparison">
        <a class="health-back" href="{{ get_post_type_archive_link('health_entry') }}">← Health journal</a>
        <header class="health-intro">
            <div><p class="health-eyebrow">THE OPEN NOTEBOOK / TRENDS</p><h1>A wider view.</h1></div>
            <p>See how the measurements change over time.<br><span>Choose one measurement to explore. Switch metrics at any time while keeping your date range.</span></p>
        </header>
        @if(!$metrics)
            <div class="health-empty"><h2>No measurements yet.</h2><p>Metrics become available here once health entries are published.</p></div>
        @else
        <form class="health-trend-controls" method="get" action="{{ route('health.compare') }}" x-data="{ search: '', selected: {{ \Illuminate\Support\Js::from($selected[0] ?? '') }}, range: {{ \Illuminate\Support\Js::from($range) }} }">
            <div class="health-trend-toolbar">
                <label>Date range<select name="range" x-model="range"><option value="7" @selected($range === '7')>Last 7 days</option><option value="30" @selected($range === '30')>Last 30 days</option><option value="90" @selected($range === '90')>Last 90 days</option><option value="all" @selected($range === 'all')>All published dates</option><option value="custom" @selected($range === 'custom')>Custom dates</option></select></label>
                <label x-show="range === 'custom'" @if($range !== 'custom') style="display: none" @endif>From<input type="date" name="from" value="{{ $from }}" :disabled="range !== 'custom'" @disabled($range !== 'custom')></label>
                <label x-show="range === 'custom'" @if($range !== 'custom') style="display: none" @endif>To<input type="date" name="to" value="{{ $to }}" :disabled="range !== 'custom'" @disabled($range !== 'custom')></label>
                <button class="journal-button" type="submit">Show trends <span aria-hidden="true">↗</span></button>
            </div>
            <details class="health-metric-picker" @if($error) open @endif>
                <summary>Change measurement <span>{{ $selected ? $metrics[$selected[0]]['label'] : 'Choose a metric' }}</span></summary>
                <label class="health-metric-search">Find a metric<input type="search" x-model="search" placeholder="Search name, topic or provider…"></label>
                <fieldset><legend class="health-picker-help">Select one measurement. Choosing another replaces the chart and keeps the date range.</legend>
                    <div class="health-metric-options">
                    @foreach($metrics as $key => $metric)
                        <label x-show="{{ \Illuminate\Support\Js::from(mb_strtolower($metric['name'])) }}.includes(search.toLowerCase())">
                            <input type="radio" name="metric" value="{{ $key }}" x-model="selected" @change="$el.form.requestSubmit()" @checked(in_array($key, $selected, true))>
                            <span>{{ $metric['label'] }} <small>{{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }} · {{ \App\Health\MetricCatalog::TOPICS[$metric['topic']] }} · {{ $metric['unit'] }} · {{ $metric['dataset'] }}{{ $metric['context'] ? ' · '.$metric['context'] : '' }}</small></span>
                        </label>
                    @endforeach
                    </div>
                </fieldset>
            </details>
        </form>
        @if($error)<p class="health-trend-error" role="alert">{{ $error }}</p>@endif
        <p class="health-trend-period"><time datetime="{{ $from }}">{{ $from }}</time> — <time datetime="{{ $to }}">{{ $to }}</time> · Europe/Bucharest · Published data only</p>
        <div class="health-trend-charts">
        @if(!$selected && !$error)<p class="health-trend-empty">Choose a measurement above, then select Show trends.</p>@endif
        @foreach($charts as $key => $chart)
            @php $metric = $metrics[$key]; $chartId = 'trend-'.$loop->index; @endphp
            <section class="health-provider health-topic-{{ $metric['topic'] }}">
                <div class="health-provider-heading"><div><p class="health-eyebrow">{{ \App\Health\MetricCatalog::TOPICS[$metric['topic']] }} / {{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }}</p><h2>{{ $metric['label'] }}</h2></div><span class="health-trend-unit">{{ $metric['unit'] === 'timestamp' ? 'Local clock time' : $metric['unit'] }}</span></div>
                <p class="health-trend-method">{{ $metric['method'] }} · {{ $metric['dataset'] }}{{ $metric['context'] ? ' · '.$metric['context'] : '' }}.</p>
                @if(!$chart['days'])
                    <p class="health-trend-empty">No published values in this date range. Try a wider range.</p>
                @else
                    @php $latest = $chart['days'][count($chart['days']) - 1]; @endphp
                    <div class="health-trend-summary"><strong>{{ \App\Health\Presentation::value(['value' => $latest['value'], 'unit' => $metric['unit']]) }} <small>{{ $metric['unit'] === 'timestamp' ? '' : $metric['unit'] }}</small></strong><span>Latest · {{ $latest['date'] }}<br>{{ count($chart['days']) }} {{ count($chart['days']) === 1 ? 'day' : 'days' }} with data</span></div>
                    <figure class="health-chart health-trend-chart">
                        <figcaption id="{{ $chartId }}">{{ $metric['label'] }} over time <span>· {{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }}</span></figcaption>
                        <div class="health-chart-range"><span>High {{ \App\Health\Presentation::number($chart['max']) }} {{ $metric['unit'] === 'timestamp' ? 'hours relative to measurement date' : $metric['unit'] }}</span><span>Low {{ \App\Health\Presentation::number($chart['min']) }} {{ $metric['unit'] === 'timestamp' ? 'hours' : $metric['unit'] }}</span></div>
                        <div class="health-trend-interactive" x-data="healthTrendTooltip" x-ref="plot" tabindex="0" role="group" aria-labelledby="{{ $chartId }}" aria-describedby="{{ $chartId }}-help"
                             @pointermove="pointAt($event)" @pointerdown="pointAt($event)" @pointerleave="if ($event.pointerType !== 'touch') hide()" @pointercancel="hide()" @focus="if (active === null) show(points.length - 1)" @blur="hide()"
                             @keydown.right.prevent="step(1)" @keydown.left.prevent="step(-1)" @keydown.home.prevent="show(0)" @keydown.end.prevent="show(points.length - 1)" @keydown.escape.prevent.stop="hide()" @resize.window="if (active !== null) show(active)">
                        <svg viewBox="0 0 640 200" role="img" aria-labelledby="{{ $chartId }}" preserveAspectRatio="none"><title>{{ count($chart['days']) }} daily values. Missing days are gaps. Exact values are in the table below.</title><path d="M12 22H628 M12 100H628 M12 178H628" class="health-chart-grid"/>
                            @foreach($chart['segments'] as $segment)<polyline points="{{ $segment }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/>@endforeach
                            @foreach($chart['points'] as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="currentColor"
                                data-date="{{ $point['day']['date'] }}" data-display="{{ \App\Health\Presentation::value(['value' => $point['day']['value'], 'unit' => $metric['unit']]) }}{{ $metric['unit'] === 'timestamp' ? '' : ' '.$metric['unit'] }}"
                                data-minimum="{{ \App\Health\Presentation::value(['value' => $point['day']['min'], 'unit' => $metric['unit']]) }}" data-maximum="{{ \App\Health\Presentation::value(['value' => $point['day']['max'], 'unit' => $metric['unit']]) }}" data-count="{{ $point['day']['count'] }}"/>@endforeach
                            <g x-show="current" style="display: none" pointer-events="none"><line :x1="current?.x" :x2="current?.x" y1="22" y2="178" class="health-trend-crosshair"/><circle :cx="current?.x" :cy="current?.y" r="5" class="health-trend-active-point" vector-effect="non-scaling-stroke"/></g>
                        </svg>
                        <div class="health-trend-tooltip" role="status" aria-live="polite" aria-atomic="true" x-show="current" style="display: none" :style="{left: tooltipLeft}">
                            <time x-text="current?.date" :datetime="current?.date"></time><strong x-text="current?.display"></strong><span>{{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }}</span>
                            <small x-show="Number(current?.count) > 1" x-text="current ? `Min ${current.minimum} · Max ${current.maximum} · ${current.count} readings` : ''"></small>
                        </div>
                        </div>
                        <div class="health-chart-range"><time datetime="{{ $from }}">{{ $from }}</time><time datetime="{{ $to }}">{{ $to }}</time></div>
                        <p class="health-trend-method" id="{{ $chartId }}-help">Hover or tap the chart for values. With the chart focused, use ← and → to move between days.</p>
                        @if(count($chart['days']) === 1)<p class="health-trend-method">One day is available. More published days will reveal a trend.</p>@endif
                        <details><summary>View daily values and source entries</summary><div class="health-table-wrap health-series-table"><table><caption>{{ $metric['label'] }} · {{ \App\Health\MetricCatalog::SOURCES[$metric['source']] }} · {{ $metric['unit'] }}</caption><thead><tr><th scope="col">Measurement date</th><th scope="col">{{ $metric['unit'] === 'timestamp' ? 'Latest time' : 'Value / average' }}</th><th scope="col">Minimum</th><th scope="col">Maximum</th><th scope="col">Readings</th></tr></thead><tbody>
                        @foreach($chart['days'] as $day)<tr><th scope="row"><a href="{{ get_permalink($day['post_id']) }}">{{ $day['date'] }} ↗</a></th>@foreach(['value', 'min', 'max'] as $field)<td>{{ \App\Health\Presentation::value(['value' => $day[$field], 'unit' => $metric['unit']]) }}</td>@endforeach<td>{{ $day['count'] }}</td></tr>@endforeach
                        </tbody></table></div></details>
                    </figure>
                @endif
            </section>
        @endforeach
        </div>
        @endif
        <p class="health-footnote">Personal measurements, not medical advice. Missing days stay empty; lines do not bridge them. Sample averages are unweighted and never added across providers. Clock-time charts use hours relative to the measurement date (negative hours mean the previous night).<br>Trends use published entries across all archive pages. The URL preserves your measurement and date range.</p>
    </div>
</x-layouts.master>
