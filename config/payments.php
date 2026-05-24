<?php

declare(strict_types=1);

return [

    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'wompi'),

    'currency' => env('PAYMENT_CURRENCY', 'COP'),

    'payment_ttl_minutes' => (int) env('PAYMENT_TTL_MINUTES', 60),

    'shipping_fee' => (float) env('PAYMENT_SHIPPING_FEE', 0),

    'gateways' => [
        'wompi' => [
            'driver' => 'wompi',
            'public_key' => env('WOMPI_PUBLIC_KEY'),
            'private_key' => env('WOMPI_PRIVATE_KEY'),
            'events_secret' => env('WOMPI_EVENTS_SECRET'),
            'integrity_secret' => env('WOMPI_INTEGRITY_SECRET'),
            'sandbox' => filter_var(env('WOMPI_SANDBOX', true), FILTER_VALIDATE_BOOL),
            'api_base' => env('WOMPI_API_BASE', 'https://sandbox.wompi.co/v1'),
            'widget_url' => env('WOMPI_WIDGET_URL', 'https://checkout.wompi.co/widget.js'),
        ],
        'paypal' => ['driver' => 'paypal'],
        'mercadopago' => ['driver' => 'mercadopago'],
        'stripe' => ['driver' => 'stripe'],
        'epayco' => ['driver' => 'epayco'],
    ],

];
