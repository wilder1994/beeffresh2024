import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/operationsPolling.js',
                'resources/js/catalogStockPolling.js',
                'resources/js/operationsMap.js',
                'resources/js/courierOps.js',
                'resources/js/orderTracking.js',
                'resources/js/paymentStatus.js',
                'resources/js/paymentProcess.js',
                'resources/js/cartValidate.js',
            ],
            refresh: true,
        }),
    ],
});
