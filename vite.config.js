import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import path from "path"
import { TanStackRouterVite } from '@tanstack/router-plugin/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        TanStackRouterVite({
            target: 'react',
            autoCodeSplitting: true,
            routesDirectory: 'resources/js/routes',
            generatedRouteTree: 'resources/js/routeTree.ts'
        }),
        react(),
        tailwindcss(),

    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
});
