import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import collectModuleAssetsPaths from './vite-module-loader.js';
import path from 'path';

async function getConfig() {
    const paths = [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/scss/custom-utils.scss',
        'resources/scss/layouts/_detail.scss',
        'resources/scss/layouts/_override_main.scss',
        'resources/scss/bootstrap.tooltip.scss',
        'resources/js/bootstrap.tooltip.js'
    ];
    const allPaths = await collectModuleAssetsPaths(paths, 'Modules');

    return defineConfig({
        plugins: [
            laravel({
                input: allPaths,
                refresh: true,
            })
        ],
        resolve: {
            alias: {
                'pkg:bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
                'pkg:@quantum': path.resolve(__dirname, 'node_modules/@quantum'),
                'pkg:@symbols-quantum': path.resolve(__dirname, 'public/fonts'),
                '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap')
            }
        },
    });
}

export default getConfig();