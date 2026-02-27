import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/exam-take.js',
                'resources/js/admin/analytics.js',
                'resources/js/admin/settings-website.js',
                'resources/js/admin/categories-index.js',
                'resources/js/admin/users.js',
                'resources/js/creator/exams-monitor.js',
                'resources/js/creator/exams-preview.js',
                'resources/js/creator/question-bank-show.js',
                'resources/js/creator/qb-question-edit.js',
                'resources/js/creator/qb-question-create.js',
                'resources/js/creator/questions-index.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
