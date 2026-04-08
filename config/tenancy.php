<?php

return [

    'central_hosts' => array_values(array_filter(array_map(
        'strtolower',
        array_map('trim', explode(',', (string) env('CENTRAL_DOMAINS', 'central.localhost,localhost,127.0.0.1')))
    ))),

    /*
    | Suggested tenant host pattern: {slug}.{tenant_domain_suffix}
    | Example: barangay "Carmen" → carmen.localhost (set in .env)
    */
    'tenant_domain_suffix' => env('TENANT_DOMAIN_SUFFIX', 'localhost'),

    /*
    | Base URL of the central app (scheme + host + port). Used for emails and links that
    | must not use tenant hosts (e.g. subscription-intent signed routes). Defaults to APP_URL.
    */
    'central_app_url' => rtrim((string) env('CENTRAL_APP_URL', env('APP_URL', 'http://localhost')), '/'),

];
