import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import { chunkSplitPlugin } from 'vite-plugin-chunk-split';
import react from '@vitejs/plugin-react'

import {dependencies} from './package.json';

function renderChunks(deps) {
    let chunks = {};
    Object.keys(deps).forEach((key) => {
        if (['react', 'react-router-dom', 'react-dom'].includes(key)) return;
        chunks[key] = [key];
    });
    return chunks;
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/react-app.tsx',
            ],
            valetTls: 'blog.test',
            refresh: true,
        }),
        react(),
    ],
    // build: {
    //     sourcemap: false,
    //     rollupOptions: {
    //         output: {
    //             manualChunks: {
    //                 vendor: ['react', 'react-router-dom', 'react-dom'],
    //                 ...renderChunks(dependencies),
    //             },
    //         },
    //     },
    // },
});
