<html>
<head>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @font-face {
            font-family: "Bariol Regular";
            font-style: normal;
            font-weight: normal;
            src: url("/wp-content/themes/pacurar2020/resources/fonts/bariol_regular-webfont.woff") format("woff");
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Inter:wght@300..700&family=JetBrains+Mono:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <style>
        html, body {
            font-family: 'Inter', sans-serif;
            background: #10141a;
            color: #e0e4e8;
            width: 100%;
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .bg-image {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .bg-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bg-image .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
        }

        .bg-fallback {
            position: absolute;
            inset: 0;
            z-index: 0;
            background: linear-gradient(135deg, #003849 0%, #10141a 60%);
        }

        .container {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            padding: 60px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }

        .logo-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .version {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(224, 228, 232, 0.5);
        }

        .middle {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.1;
            color: #fff;
            max-width: 900px;
        }

        .bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .meta {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .meta-item {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(224, 228, 232, 0.6);
        }

        .tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
            max-width: 600px;
        }

        .tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 6px 16px;
            background: rgba(94, 196, 219, 0.2);
            color: #5ec4db;
            border-radius: 2px;
            border: 1px solid rgba(94, 196, 219, 0.3);
        }

        .streak {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #5ec4db;
            margin-top: 4px;
        }

        .accent-line {
            width: 60px;
            height: 3px;
            background: #5ec4db;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    @php
        $featured_image = get_the_post_thumbnail_url($post->id, 'full');
        $p = $post->wpPost();
        $tags = get_the_tags($p) ?: [];
        $categories = get_the_terms($p, 'category') ?: [];
        $streak = get_option('current_daily_streak_100d');
        $date = get_the_date('Y . m . d', $p);
    @endphp

    @if($featured_image)
        <div class="bg-image">
            <img src="{{ $featured_image }}" alt="">
            <div class="overlay"></div>
        </div>
    @else
        <div class="bg-fallback"></div>
    @endif

    <div class="container">
        {{-- Top: Logo + version --}}
        <div class="top">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1355 493" style="max-width: 250px; min-width: 250px;">
                    <defs/>
                    <image width="442" height="442" x="21" y="18" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAboAAAG6CAMAAABJKXO3AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACnVBMVEUALzz///8ALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwALzwAAAD8JAJdAAAA3XRSTlMAAAkOEBEtebbR3uLo7O7t6+fj1LNwKQ0HDzZQWl50svn74atxXU0GARdLl8vl9fTk3cSHOggFAi9hgI6w/teliXtVIhtDg8r9uTUYP7za+vI7HFuWkRIaa7jY6apTDDl2wvOvZzQUSvjTRShoqdtiHyx4w/G/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"/>
                    <text x="237.843" y="384.75" fill="#fff" font-family="Bariol" font-size="430" text-anchor="middle">
                        <tspan x="237.843">f</tspan>
                    </text>
                    <text x="908.885" y="290.25" fill="#e0e4e8" data-name="ilip pacurar" font-family="Bariol Regular" font-size="200" text-anchor="middle">
                        <tspan x="908.885">ilip pacurar</tspan>
                    </text>
                </svg>
            </div>
            <div class="version">
                {{ $post->ogImageHost() }}
            </div>
        </div>

        {{-- Middle: Title --}}
        <div class="middle">
            <div>
                <div class="accent-line"></div>
                <div class="title">{!! $post->title !!}</div>
            </div>
        </div>

        {{-- Bottom: Date + streak + tags --}}
        <div class="bottom">
            <div class="meta">
                <div class="meta-item">{{ $date }}</div>
                @if($streak > 0)
                    <div class="streak">#writeDaily // {{ $streak }}d streak</div>
                @endif
            </div>
            <div class="tags">
                @foreach($categories as $cat)
                    <span class="tag">{!! $cat->name !!}</span>
                @endforeach
                @foreach($tags as $tag)
                    <span class="tag"># {!! $tag->name !!}</span>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
