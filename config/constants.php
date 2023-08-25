<?php

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET'),
    'shopify_scope' => env('SHOPIFY_SCOPES'),
    'shopify_api_version' => env('SHOPIFY_API_VERSION'),

    'app_name' => env('APP_NAME'),

    'app_version' => 4,
    'pagination' => 10,

    'shopify_webhooks' => [
        'app/uninstalled',
        'shop/update',
        'orders/create',
        'orders/updated',
        'orders/delete',
        'locations/activate',
        'locations/create',
        'locations/deactivate',
        'locations/delete',
        'locations/update',
    ],
];
