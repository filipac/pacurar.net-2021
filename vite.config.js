import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            valetTls: 'blog.test',
            // refresh: true,
        }),
    ],
    experimental: {
        renderBuiltUrl(filename, {hostType}) {
            if (hostType === 'js') {
                return {runtime: `window.__toCdnUrl(${JSON.stringify(filename)})`}
            } else {
                return {relative: true}
            }
        }
    }
});
