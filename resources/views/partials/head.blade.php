<head>
    <title>{{$attributes->get('title', view('partials.title'))}}</title>
    <meta name="theme-color" content="#003849">
    <script>
        (function() {
            var stored = localStorage.getItem('darkMode');
            var d = stored !== null ? stored === 'true' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            var g = localStorage.getItem('geekMode') === 'true';
            if (d) document.documentElement.classList.add('dark');
            if (g) document.documentElement.classList.add('geek-mode');
        })();
    </script>

    @include('partials.inside_head')
</head>
