@if($comparison)
    @php
        $basis = $comparison['current']['unit'] === 'timestamp' ? 'Local clock time' : ($comparison['current']['count'] > 1 || str_contains($comparison['current']['method'], 'sample') ? 'Daily average' : 'Daily value');
        $weeklyLabel = $comparison['chart'] ? implode('; ', array_map(fn ($day) => $day['date'].': '.\App\Health\Presentation::value(['value' => $day['value'], 'unit' => $comparison['current']['unit']]), $comparison['chart']['days'])) : '';
    @endphp
    <div class="health-comparison {{ ($compact ?? false) ? 'health-comparison-compact' : '' }}">
        @if($comparison['deltas'])
            <dl class="health-deltas">
                @foreach($comparison['deltas'] as $delta)
                    <div><dt title="{{ $delta['date'] }}">{{ $delta['offset'] === 1 ? 'vs previous day' : 'vs 7 days earlier' }}</dt><dd title="{{ $basis }}; {{ $comparison['current']['unit'] === '%' ? 'pp = percentage points' : $comparison['current']['unit'] }}">{{ $delta['text'] }}</dd></div>
                @endforeach
            </dl>
        @endif
        @if($comparison['chart'])
            <div class="health-week">
                <svg viewBox="0 0 640 200" preserveAspectRatio="none" role="img" aria-label="7-day trend: {{ $weeklyLabel }}. Missing days are gaps.">
                    @foreach($comparison['chart']['segments'] as $segment)<polyline points="{{ $segment }}" fill="none" stroke="currentColor" stroke-width="2" vector-effect="non-scaling-stroke"/>@endforeach
                    @foreach($comparison['chart']['points'] as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="currentColor"/>@endforeach
                </svg>
                <span>7-day trend <small>{{ count($comparison['chart']['days']) }}/7 days</small></span>
            </div>
        @endif
        <p class="health-comparison-basis">{{ $basis }} · <a href="{{ route('health.compare', ['metric' => $comparison['key'], 'range' => 'custom', 'from' => $comparison['from'], 'to' => $comparison['date']]) }}">Explore week <span aria-hidden="true">↗</span></a></p>
        @if($comparison['chart'] && ! ($compact ?? false))
            <details class="health-week-data"><summary>Weekly values · {{ $comparison['from'] }}–{{ $comparison['date'] }}</summary>
                <table><caption>{{ $basis }} · {{ $comparison['current']['unit'] === 'timestamp' ? 'Europe/Bucharest' : $comparison['current']['unit'] }}</caption><thead><tr><th scope="col">Date</th><th scope="col">Value</th></tr></thead><tbody>
                    @foreach($comparison['chart']['days'] as $day)<tr><th scope="row">{{ $day['date'] }}</th><td>{{ \App\Health\Presentation::value(['value' => $day['value'], 'unit' => $comparison['current']['unit']]) }}</td></tr>@endforeach
                </tbody></table>
            </details>
        @endif
    </div>
@endif
