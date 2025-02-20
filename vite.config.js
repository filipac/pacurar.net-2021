import {defineConfig, splitVendorChunkPlugin} from 'vite';
import laravel from 'laravel-vite-plugin';
import {chunkSplitPlugin} from 'vite-plugin-chunk-split';
import react from '@vitejs/plugin-react'

import {dependencies} from './package.json';
import {NodeGlobalsPolyfillPlugin} from "@esbuild-plugins/node-globals-polyfill";
import inject from '@rollup/plugin-inject'
import {visualizer} from "rollup-plugin-visualizer";
import fs from 'fs';
function renderChunks(deps) {
    let chunks = {};
    Object.keys(deps).forEach((key) => {
        if (['react', 'react-router-dom', 'react-dom'].includes(key)) return;
        chunks[key] = [key];
    });
    return chunks;
}

const define = process.env.NODE_ENV !== 'development' ? {} : {
    global: '({tinymce: window.tinymce})',
}
const dfine2 = process.env.NODE_ENV !== 'development' ? {} : {
                global: 'globalThis',
                // _global: ({}),
            }

export default defineConfig({
    https: {
        key: fs.readFileSync('/Users/filipac/Library/Application Support/Herd/config/valet/Certificates/blog.test.key'),
        cert: fs.readFileSync('/Users/filipac/Library/Application Support/Herd/config/valet/Certificates/blog.test.crt'),
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/react-app.tsx',
            ],
            detectTls: 'blog.test',
            refresh: true,
        }),
        react(),
        splitVendorChunkPlugin(),
        visualizer(),
    ],
    define,
    // base: '/wp-content/themes/pacurar2020/dist/',
    // preserveSymlinks: true,
    // publicDir: 'resources',
    build: {

        modulePreload: false,
        commonjsOptions: {
            transformMixedEsModules: true
        },
        rollupOptions: {
            plugins: [
                inject({Buffer: ['buffer', 'Buffer']})
            ],
            output: {
                manualChunks: function (id) {
                    if (id.includes('bignumber')) {
                        return 'bignumber';
                    }
                    if (id.includes('lodash')) {
                        return 'lodash';
                    }
                    // if(id.includes('react-dom') || id.includes('react-router-dom') || id.includes('react') || id.includes('recoil')) {
                    //     return 'react-dom';
                    // }
                    // if (id.includes('@multiversx')) {
                    //     return 'multiversx-sdk';
                    // }
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }


                }
            }
        }
    },
    optimizeDeps: {
        esbuildOptions: {
            // Node.js global to browser globalThis
            define: dfine2,
            // Enable esbuild polyfill plugins
            plugins: [
                process.env.NODE_ENV === 'production' && NodeGlobalsPolyfillPlugin({
                    buffer: true
                })
            ].filter(Boolean)
        }
    },
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
